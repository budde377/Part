<?php
namespace ChristianBudde\cbweb\controller\ajax;
use ChristianBudde\cbweb\BackendSingletonContainer;
use ChristianBudde\cbweb\controller\function_string\ParserImpl;
use ChristianBudde\cbweb\controller\json\CompositeFunction;
use ChristianBudde\cbweb\controller\json\JSONFunction;
use ChristianBudde\cbweb\controller\json\JSONFunctionImpl;
use ChristianBudde\cbweb\controller\json\ParserImpl as JSONParser;
use ChristianBudde\cbweb\controller\json\Program;
use ChristianBudde\cbweb\controller\json\Response;
use ChristianBudde\cbweb\controller\json\ResponseImpl;
use ChristianBudde\cbweb\controller\json\Target;
use ChristianBudde\cbweb\controller\json\Type;
use ChristianBudde\cbweb\exception\ClassNotDefinedException;
use ChristianBudde\cbweb\exception\ClassNotInstanceOfException;
use ChristianBudde\cbweb\exception\FileNotFoundException;
use ReflectionClass;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 3:44 PM
 */
class ServerImpl implements Server
{


    private $handlers = [];

    private $backendSingletonContainer;

    private $jsonParser;
    private $functionStringParser;



    function __construct(BackendSingletonContainer $backendSingletonContainer)
    {

        $this->backendSingletonContainer = $backendSingletonContainer;
        $this->jsonParser = new JSONParser();
        $this->functionStringParser = new ParserImpl();

    }


    /**
     * Registers a AJAX type.
     * @param TypeHandler $type
     * @return void
     */
    public function registerHandler(TypeHandler $type)
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


            if (!($handler instanceof TypeHandler)) {
                throw new ClassNotInstanceOfException($className, 'AJAXTypeHandler');
            }

            $this->registerHandler($handler);
        }
    }

    /**
     * @param string $input
     * @param string $token
     * @return Response
     */
    public function handleFromJSONString($input, $token = null)
    {
        return $this->wrapperHandler($this->jsonParser->parse($input), $token);
    }

    /**
     * @param string $token
     * @return Response
     */
    public function handleFromRequestBody($token = null)
    {
        return $this->wrapperHandler($this->jsonParser->parseFromRequestBody(), $token);
    }

    /**
     * @param $input
     * @param $token
     * @return Response
     */

    private function wrapperHandler($input, $token){

        if(!$this->backendSingletonContainer->getUserLibraryInstance()->verifyUserSessionToken($token)){
            return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_UNAUTHORIZED);
        }

        if (!($input instanceof Program)) {
            return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_MALFORMED_REQUEST);
        }

        $result = $this->internalHandleProgram($input);



        if ($result === null) {
            $result = new ResponseImpl();
        } else if(!(($r = $result) instanceof Response)) {
            $result = new ResponseImpl();
            $result->setPayload($r);
        }

        if(($id = $input->getId()) != null){
            $result->setID($id);
        }
        return $result;

    }

    /**
     * @param Program $input
     * @return mixed
     */
    private function internalHandleProgram(Program $input)
    {
        $result = null;
        if($input instanceof CompositeFunction){


            if(($target = $input->getTarget()) instanceof Type){

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
     * @param Target $targetOverride
     * @param mixed $overrideInstance
     * @return mixed
     */
    private function internalHandleFunction(JSONFunction $function, Target $targetOverride = null, $overrideInstance = null)
    {



        $target = $function->getTarget();
        $types = [];
        $instance = null;

        $args = [];
        foreach($function->getArgs() as $arg){
            if(!($arg instanceof Program)){
                $args[] = $arg;
                continue;
            }

            $argumentResponse = $this->internalHandleProgram($arg);
            if($argumentResponse instanceof Response){
                return $argumentResponse;
            }

            $args[] = $argumentResponse;

        }


        $function = new JSONFunctionImpl($function->getName(), $target, $args);


        if ($target instanceof Type) {

            if (!isset($this->handlers[$type = $target->getTypeString()])) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_NO_SUCH_FUNCTION);
            }
            $types[] = $target->getTypeString();

        } else if ($target instanceof JSONFunction) {
            $instance = $target == $targetOverride?$overrideInstance:$this->internalHandleFunction($target, $targetOverride, $overrideInstance);
            if(is_array($instance)){
                $types = ['array'];

            } else {
                if (!is_object($instance)) {
                    return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_NO_SUCH_FUNCTION);
                }

                if ($instance instanceof Response) {
                    return $instance;
                }

                $reflection = new ReflectionClass($instance);
                $types = $this->buildType($reflection);
            }

        } else {
            return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_MALFORMED_REQUEST);
        }

        foreach ($types as $type) {
            if (!isset($this->handlers[$type])) {
                continue;
            }
            foreach ($this->handlers[$type] as $h) {
                /** @var $h TypeHandler */
                if (!$h->canHandle($type, $function, $instance)) {
                    continue;
                }
                return $h->handle($type, $function, $instance);
            }

        }
        return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_NO_SUCH_FUNCTION);
    }

    /**
     * @param string $input
     * @param string $token
     * @return Response
     */
    public function handleFromFunctionString($input, $token = null)
    {

        return $this->wrapperHandler(($pr = $this->functionStringParser->parseString($input)) instanceof \ChristianBudde\cbweb\controller\function_string\ast\Program?$pr->toJSONProgram():null, $token);
    }

    private function buildType(ReflectionClass $reflection)
    {
        $result = [];

        foreach($i = $reflection->getInterfaces() as $class){
            $result[] = $class->getName();
            $result = array_merge($result, $this->buildType($class));
        }

        if($parent = $reflection->getParentClass()){
            $result = array_merge($result, $this->buildType($parent));
        }

        $r = [];

        while(count($result)){
            $e = array_pop($result);
            if(in_array($e, $result)){
                continue;
            }
            $r[] = $e;
        }

        return $r;
    }


}