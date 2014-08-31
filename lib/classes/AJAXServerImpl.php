<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 3:44 PM
 */
class AJAXServerImpl implements AJAXServer
{


    private $handlers = [];

    private $backendSingletonContainer;

    private $jsonParser;
    private $functionStringParser;



    function __construct(BackendSingletonContainer $backendSingletonContainer)
    {

        $this->backendSingletonContainer = $backendSingletonContainer;
        $this->jsonParser = new JSONParserImpl();
        $this->functionStringParser = new FunctionStringParserImpl();
    }


    /**
     * Registers a AJAX type.
     * @param AJAXTypeHandler $type
     * @return void
     */
    public function registerHandler(AJAXTypeHandler $type)
    {
        $zeroLength = true;
        foreach ($type->listTypes() as $t) {
            $zeroLength = false;
            $this->handlers[$t][] = $type;
            $type->setUp($this, $t);
        }

        if ($zeroLength) {
            $type->setUp($this, null);
        }
    }

    /**
     * Registers the handlers from config.
     * @throws ClassNotDefinedException
     * @throws FileNotFoundException
     * @throws ClassNotInstanceOfException
     * @return void
     */
    public function registerHandlersFromConfig()
    {
        $config = $this->backendSingletonContainer->getConfigInstance();
        foreach ($config->getAJAXTypeHandlers() as $handlerArray) {

            if (isset($handlerArray['path'])) {
                $path = $handlerArray['path'];
                if (!file_exists($path)) {
                    throw new FileNotFoundException($path, "AJAXTypeHandler class file");
                }
                require_once $path;
            }

            $className = $handlerArray['class_name'];

            if (!class_exists($className)) {
                throw new ClassNotDefinedException($className);
            }


            $handler = new $className($this->backendSingletonContainer);


            if (!($handler instanceof AJAXTypeHandler)) {
                throw new ClassNotInstanceOfException($className, 'AJAXTypeHandler');
            }

            $this->registerHandler($handler);
        }
    }

    /**
     * @param string $input
     * @return JSONResponse
     */
    public function handleFromJSONString($input)
    {
        return $this->wrapper($this->jsonParser->parse($input));
    }

    /**
     * @return JSONResponse
     */
    public function handleFromRequestBody()
    {
        return $this->wrapper($this->jsonParser->parseFromRequestBody());
    }

    /**
     * @param $input
     * @return JSONResponseImpl
     */

    private function wrapper($input)
    {
        if (!($input instanceof JSONFunction)) {
            return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_MALFORMED_REQUEST);
        }

        $result = $this->internalHandle($input);

        if ($result == null) {
            $result = new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_NOT_IMPLEMENTED);
        } else if(!(($r = $result) instanceof JSONResponse)) {
            $result = new JSONResponseImpl();
            $result->setPayload($r);

        }
        $result->setID($input->getId());
        return $result;
    }

    /**
     * @param JSONFunction $function
     * @return mixed
     */
    private function internalHandle( $function)
    {


        $target = $function->getTarget();
        $types = [];
        $instance = null;

        foreach($function->getArgs() as $num => $arg){
            if(!($arg instanceof JSONFunction)){
                continue;
            }

            $argumentResponse = $this->internalHandle($arg);
            if($argumentResponse instanceof JSONResponse){
                return $argumentResponse;
            }

            $function->setArg($num, $argumentResponse);

        }

        if ($target instanceof JSONType) {

            if (!isset($this->handlers[$type = $target->getTypeString()])) {
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_NO_SUCH_FUNCTION);
            }
            $types[] = $target->getTypeString();

        } else if ($target instanceof JSONFunction) {
            $instance = $this->internalHandle($target);
            if (!is_object($instance)) {
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_NO_SUCH_FUNCTION);
            }

            if ($instance instanceof JSONResponse) {
                return $instance;
            }

            $reflection = new ReflectionClass($instance);
            $types = $reflection->getInterfaceNames();

        }

        foreach ($types as $type) {
            if (!isset($this->handlers[$type])) {
                continue;
            }
            foreach ($this->handlers[$type] as $h) {
                /** @var $h AJAXTypeHandler */
                if (!$h->canHandle($type, $function, $instance)) {
                    continue;
                }
                return $h->handle($type, $function, $instance);
            }

        }
        return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_MALFORMED_REQUEST);
    }

    /**
     * @param string $input
     * @return JSONResponse
     */
    public function handleFromFunctionString($input)
    {
        $this->functionStringParser->parseFunctionCall($input, $result);
        return $this->wrapper($result);
    }



}