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

    function __construct(BackendSingletonContainer $backendSingletonContainer)
    {
        $this->backendSingletonContainer = $backendSingletonContainer;
        $this->jsonParser = new JSONParserImpl();
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
        $result = $this->internalHandle($input);
        if ($result == null) {
            return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_NOT_IMPLEMENTED);
        }
        if ($result instanceof JSONResponse) {
            return $result;
        }
        $response = new JSONResponseImpl();
        $response->setPayload($result);
        return $response;
    }

    /**
     * @param JSONFunction $function
     * @return mixed
     */
    private function internalHandle(JSONFunction $function)
    {
        if (!($function instanceof JSONFunction)) {
            return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_MALFORMED_REQUEST);
        }

        $target = $function->getTarget();
        $types = [];
        $instance = null;

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
        $func = $this->parseFunctionString($input);
        return $this->wrapper($func);
    }

    /**
     * <function_call>  = <target>.<function>
     * <function>       = <name>([<arg>,...]*)
     * <target>         = <function_call> | <name>
     * <arg>            = <scalar> | <array> | <target>
     * <array>          = \[ <array_index>, ... \]
     * <array_index>    = <scalar> => <arg> | <arg>
     * <scalar>         = true | false | null | *num* | *string*
     * @param string $input
     * @return JSONFunction
     */

    private function parseFunctionString($input)
    {
        return $this->parseFunctionCall($input, $result)?$result:null;
    }

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    private function parseFunctionCall($input, &$result)
    {
        preg_match_all('/\./', $input, $matches, PREG_OFFSET_CAPTURE);
        $matches = array_reverse($matches);

        foreach ($matches as $match) {
            if ($this->parseFunction(substr($input, $match[1] + 1), $resultFunction) &&
                $this->parseTarget(substr($input, 0, $match[1]), $resultTarget)) {
                /** @var $resultTarget JSONTarget */
                /** @var $resultFunction JSONFunction */
                $resultFunction->setTarget($resultTarget);
                $result = $resultFunction;
                return true;
            }
        }
        return false;
    }

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    private function parseFunction($input, &$result)
    {
    }

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    private function parseTarget($input, &$result)
    {
        return $this->parseName($input, $result) || $this->parseFunctionCall($input, $result);
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    private function parseName($input, &$result)
    {
        if($r = preg_match('/[a-z0-9_]/i', $input)){
            $result = $input;
        }

        return $r == 1;
    }

    // TODO test for functions in arguments


}