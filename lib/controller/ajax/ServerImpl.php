<?php
namespace ChristianBudde\Part\controller\ajax;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\function_string\ParserImpl;
use ChristianBudde\Part\controller\json\CompositeFunction;
use ChristianBudde\Part\controller\json\JSONFunction;
use ChristianBudde\Part\controller\json\JSONFunctionImpl;
use ChristianBudde\Part\controller\json\ParserImpl as JSONParser;
use ChristianBudde\Part\controller\json\Program;
use ChristianBudde\Part\controller\json\Response;
use ChristianBudde\Part\controller\json\ResponseImpl;
use ChristianBudde\Part\controller\json\Target;
use ChristianBudde\Part\controller\json\Type;
use ChristianBudde\Part\exception\ClassNotDefinedException;
use ChristianBudde\Part\exception\ClassNotInstanceOfException;
use ChristianBudde\Part\exception\FileNotFoundException;
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

    private $container;

    private $jsonParser;
    private $functionStringParser;


    function __construct(BackendSingletonContainer $container)
    {

        $this->container = $container;
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
        $config = $this->container->getConfigInstance();
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

            if (in_array('ChristianBudde\Part\controller\ajax\TypeHandlerGenerator', $ar = class_implements($className))) {
                $handler = call_user_func($className . '::generateTypeHandler', $this->container);
            } else {
                $handler = new $className($this->container);
            }


            if (!($handler instanceof TypeHandler)) {
                throw new ClassNotInstanceOfException($handler == null ? 'null' : get_class($handler), 'AJAXTypeHandler');
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

    private function wrapperHandler($input, $token)
    {

        if (!$this->container->getUserLibraryInstance()->verifyUserSessionToken($token)) {
            return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_UNAUTHORIZED);
        }

        if (!($input instanceof Program)) {
            return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_MALFORMED_REQUEST);
        }

        $result = $this->internalHandleProgram($input);


        if ($result === null) {
            $result = new ResponseImpl();
        } else if (!(($result_payload = $result) instanceof Response)) {
            $result = new ResponseImpl();
            $result->setPayload($result_payload);
        }

        if (($input_id = $input->getId()) != null) {
            $result->setID($input_id);
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
        if ($input instanceof CompositeFunction) {


            if (($target = $input->getTarget()) instanceof Type) {

                foreach ($input->listFunctions() as $function) {
                    $result = $this->internalHandleFunction($function);
                }


            } else if ($target instanceof JSONFunction) {

                $instance = $this->internalHandleFunction($target);

                foreach ($input->listFunctions() as $function) {
                    $result = $this->internalHandleFunction($function, $target, $instance);
                }

            }

        } else if ($input instanceof JSONFunction) {
            $result = $this->internalHandleFunction($input);
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

        $args = $this->internalHandleArguments($function);
        if ($args instanceof Response) {
            return $args;
        }
        $function = new JSONFunctionImpl($function->getName(), $target, $args);

        if ($target instanceof Type) {
            return $this->internalHandleFunctionBase($target, $function);

        }

        if ($target instanceof JSONFunction) {
            return $this->internalHandleFunctionInduction($target, $targetOverride, $overrideInstance, $function);

        }
        return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_MALFORMED_REQUEST);


    }

    /**
     * @param string $input
     * @param string $token
     * @return Response
     */
    public function handleFromFunctionString($input, $token = null)
    {

        return $this->wrapperHandler(($pr = $this->functionStringParser->parseString($input)) instanceof \ChristianBudde\Part\controller\function_string\ast\Program ? $pr->toJSONProgram() : null, $token);
    }

    private function buildType(ReflectionClass $reflection)
    {
        $result = [];

        foreach ($reflection->getInterfaces() as $class) {
            $result[] = $class->getName();
            $result = array_merge($result, $this->buildType($class));
        }

        if ($parent = $reflection->getParentClass()) {
            $result = array_merge($result, $this->buildType($parent));
        }


        return array_unique($result);
    }

    private function internalHandleArguments(JSONFunction $function)
    {
        $args = [];
        foreach ($function->getArgs() as $arg) {
            if (!($arg instanceof Program)) {
                $args[] = $arg;
                continue;
            }

            $argumentResponse = $this->internalHandleProgram($arg);
            if ($argumentResponse instanceof Response) {
                return $argumentResponse;
            }

            $args[] = $argumentResponse;

        }
        return $args;
    }

    private function internalHandleFunctionBase(Type $target, JSONFunction $function)
    {

        if (!isset($this->handlers[$type = $target->getTypeString()])) {
            return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_NO_SUCH_FUNCTION);
        }

        return $this->handle([$target->getTypeString()], $function);
    }


    private function handle(array $types, JSONFunction $function, $instance = null, $generatedHandler = null)
    {
        foreach ($types as $type) {

            if ($generatedHandler instanceof TypeHandler && $generatedHandler->canHandle($type, $function, $instance)) {
                return $generatedHandler->handle($type, $function, $instance);
            }
            if (!isset($this->handlers[$type])) {
                continue;
            }

            foreach ($this->handlers[$type] as $handler) {
                /** @var $handler TypeHandler */
                if (!$handler->canHandle($type, $function, $instance)) {
                    continue;
                }
                return $handler->handle($type, $function, $instance);
            }

        }

        return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_NO_SUCH_FUNCTION);
    }

    private function internalHandleFunctionInduction(JSONFunction $target, $targetOverride, $overrideInstance, JSONFunction $function)
    {
        $instance = $target == $targetOverride ? $overrideInstance : $this->internalHandleFunction($target, $targetOverride, $overrideInstance);
        if (is_array($instance)) {
            return $this->handle(['array'], $function, $instance);

        }

        if (!is_object($instance)) {
            return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_NO_SUCH_FUNCTION);
        }

        if ($instance instanceof Response) {
            return $instance;
        }

        if ($instance instanceof TypeHandlerGenerator) {
            $generatedHandler = $instance->generateTypeHandler();
        } else {
            $generatedHandler = null;
        }

        $reflection = new ReflectionClass($instance);
        $types = $this->buildType($reflection);

        return $this->handle($types, $function, $instance, $generatedHandler);
    }


}