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
        return $this->wrapperHandler($this->jsonParser->parse($input));
    }

    /**
     * @return JSONResponse
     */
    public function handleFromRequestBody()
    {
        return $this->wrapperHandler($this->jsonParser->parseFromRequestBody());
    }

    /**
     * @param $input
     * @return JSONResponseImpl
     */

    private function wrapperHandler($input){

        if (!($input instanceof JSONProgram)) {
            return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_MALFORMED_REQUEST);
        }

        $result = $this->internalHandleProgram($input);



        if ($result == null) {
            $result = new JSONResponseImpl();
        } else if(!(($r = $result) instanceof JSONResponse)) {
            $result = new JSONResponseImpl();
            $result->setPayload($r);
        }

        if(($id = $input->getId()) != null){
            $result->setID($id);
        }
        return $result;

    }

    /**
     * @param JSONProgram $input
     * @return mixed
     */
    private function internalHandleProgram(JSONProgram $input)
    {
        $result = null;
        if($input instanceof JSONCompositeFunction){


            if(($target = $input->getTarget()) instanceof JSONType){

                foreach($input->listFunctions() as $function){
                    $result = $this->internalHandleFunction($function);
                }


            } else if($target instanceof JSONFunction) {

                $instance = $this->internalHandleFunction($target);

                foreach($input->listFunctions() as $function){
                    $result = $this->internalHandleFunction($function, $target, $instance);
                }

            }

        } else if($input instanceof JSONFunction){
            $result =  $this->internalHandleFunction($input);
        }

        return $result;

    }

    /**
     * @param JSONFunction $function
     * @param \JSONTarget $targetOverride
     * @param mixed $overrideInstance
     * @return mixed
     */
    private function internalHandleFunction(JSONFunction $function, JSONTarget $targetOverride = null, $overrideInstance = null)
    {



        $target = $function->getTarget();
        $types = [];
        $instance = null;

        foreach($function->getArgs() as $num => $arg){
            if(!($arg instanceof JSONProgram)){
                continue;
            }

            $argumentResponse = $this->internalHandleProgram($arg);
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
            $instance = $target === $targetOverride?$overrideInstance:$this->internalHandleFunction($target, $targetOverride, $overrideInstance);
            if (!is_object($instance)) {
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_NO_SUCH_FUNCTION);
            }

            if ($instance instanceof JSONResponse) {
                return $instance;
            }

            $reflection = new ReflectionClass($instance);
            $types = $reflection->getInterfaceNames();

        } else {
            return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_MALFORMED_REQUEST);
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
        return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_NO_SUCH_FUNCTION);
    }

    /**
     * @param string $input
     * @return JSONResponse
     */
    public function handleFromFunctionString($input)
    {

        return $this->wrapperHandler($this->functionStringParser->parseFunctionString($input));
    }



}