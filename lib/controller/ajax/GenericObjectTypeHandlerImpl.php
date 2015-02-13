<?php
namespace ChristianBudde\Part\controller\ajax;

use ChristianBudde\Part\controller\json\JSONFunction;
use ChristianBudde\Part\controller\json\Response;
use ChristianBudde\Part\controller\json\ResponseImpl;
use ReflectionClass;
use ReflectionMethod;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/31/14
 * Time: 7:32 PM
 */
class GenericObjectTypeHandlerImpl implements TypeHandler
{


    private $object;
    private $types = [];
    private $typeWhitelist = [];
    private $functions = [];
    private $functionWhitelist = [];
    private $customFunctions = [];

    private $authFunctions = [];
    private $typeAuthFunctions = [];
    private $functionAuthFunctions = [];

    private $preCallFunctions = [];
    private $typePreCallFunctions = [];
    private $functionPreCallFunctions = [];


    private $postCallFunctions = [];
    private $typePostCallFunctions = [];
    private $functionPostCallFunctions = [];

    private $alias = [];
    private $hasBeenSetUp = [];


    function __construct($object)
    {

        if (is_string($object)) {
            $this->types[] = $object;
            $i = false;
            if (!class_exists($object) && !($i = interface_exists($object))) {
                return;
            }
            $reflection = new ReflectionClass($object);
            $sn = $reflection->getShortName();
            if ($i && preg_match("/\\\\$sn/", $object)) {
                $this->types[] = $sn;
                $this->alias[$sn][] = $object;
            }
        } else {
            $this->object = $object;
            $reflection = new ReflectionClass($object);
        }


        $this->types = array_merge($this->types, $reflection->getInterfaceNames());

        $alias = array_values(array_map(function (ReflectionClass $class) {
            $s = $class->getShortName();
            $found = false;
            foreach ($this->types as $t) {
                if (preg_match("/\\\\$s/", $t)) {
                    $this->alias[$s][] = $t;
                    $found = true;
                }
            }


            return $found ? $s : null;
        }, $reflection->getInterfaces()));

        $alias = array_filter($alias, function ($s) {
            return $s != null;
        });
        $this->types = array_values(array_merge($this->types, $alias));

        if (func_num_args() == 1) {
            return;
        }

        call_user_func_array(array($this, 'whitelistType'), array_slice(func_get_args(), 1));
    }


    /**
     * Adds an auth function of type: f(type, instance, function_name, arguments) => bool
     * @param callable $function
     */
    public function addAuthFunction(callable $function)
    {
        $this->authFunctions[] = $function;
    }

    /**
     * Adds an auth function of type: f(type, instance, function_name, arguments) => bool
     * @param string $type
     * @param string $functionName
     * @param callable $function
     */
    public function addFunctionAuthFunction($type, $functionName, callable $function)
    {
        if (isset($this->alias[$type])) {
            foreach ($this->alias[$type] as $target) {
                $this->addFunctionAuthFunction($target, $functionName, $function);
            }
            return;
        }
        $this->functionAuthFunctions[$type][$functionName][] = $function;
    }

    /**
     * Adds an auth function of type: f(type, instance, function_name, arguments) => bool
     * @param string $type
     * @param callable $function
     */
    public function addTypeAuthFunction($type, callable $function)
    {
        if (isset($this->alias[$type])) {
            foreach ($this->alias[$type] as $target) {
                $this->addTypeAuthFunction($target, $function);
            }
            return;
        }
        $this->typeAuthFunctions[$type][] = $function;
    }

    /**
     * Adds a function of type: f(instance, arguments ... ) => mixed
     * @param string $type
     * @param string $name
     * @param callable $function
     */
    public function addFunction($type, $name, callable $function)
    {
        if (isset($this->alias[$type])) {
            foreach ($this->alias[$type] as $target) {
                $this->addFunction($target, $name, $function);
            }
            return;
        }

        $this->customFunctions[$type][$name] = $function;
    }

    /**
     * If added function will be called before the function.
     * The function should be of type : f(instance, &arguments) => void
     * @param callable $function
     */
    public function addPreCallFunction(callable $function)
    {
        $this->preCallFunctions[] = $function;
    }

    /**
     * If added function will be called after the function.
     * The function should be of type : f(instance, &result) => void
     * @param callable $function
     */
    public function addPostCallFunction(callable $function)
    {
        $this->postCallFunctions[] = $function;
    }

    /**
     * If added function will be called before the function.
     * The function should be of type : f(instance, &arguments) => void
     * @param $type
     * @param callable $function
     */
    public function addTypePreCallFunction($type, callable $function)
    {
        if (isset($this->alias[$type])) {
            foreach ($this->alias[$type] as $target) {
                $this->addTypePreCallFunction($target, $function);
            }
            return;
        }

        $this->typePreCallFunctions[$type][] = $function;
    }

    /**
     * If added function will be called after the function.
     * The function should be of type : f(instance, &result) => void
     * @param $type
     * @param callable $function
     */
    public function addTypePostCallFunction($type, callable $function)
    {
        if (isset($this->alias[$type])) {
            foreach ($this->alias[$type] as $target) {
                $this->addTypePostCallFunction($target, $function);
            }
            return;
        }

        $this->typePostCallFunctions[$type][] = $function;
    }

    /**
     * If added function will be called before the function.
     * The function should be of type : f(instance, &arguments) => void
     * @param $type
     * @param $name
     * @param callable $function
     */
    public function addFunctionPreCallFunction($type, $name, callable $function)
    {
        if (isset($this->alias[$type])) {
            foreach ($this->alias[$type] as $target) {
                $this->addFunctionPreCallFunction($target, $name, $function);
            }
            return;
        }

        $this->functionPreCallFunctions[$type][$name][] = $function;
    }

    /**
     * If added function will be called after the function.
     * The function should be of type : f(instance, &result) => void
     * @param $type
     * @param $name
     * @param callable $function
     */
    public function addFunctionPostCallFunction($type, $name, callable $function)
    {
        if (isset($this->alias[$type])) {
            foreach ($this->alias[$type] as $target) {
                $this->addFunctionPostCallFunction($target, $name, $function);
            }
            return;
        }
        $this->functionPostCallFunctions[$type][$name][] = $function;
    }

    /**
     * Whitelists a type, if no type is whitelisted; all found types are whitelisted.
     * @param string $type , ...
     */
    public function whitelistType($type)
    {
        foreach (func_get_args() as $arg) {
            if (!in_array($arg, $this->types)) {
                continue;
            }
            if (isset($this->alias[$arg])) {
                call_user_func_array([$this, "whitelistType"], $this->alias[$arg]);
            }
            $this->typeWhitelist[] = $arg;

        }

    }

    /**
     * Whitelists a function, if no function is whitelisted; all found types are whitelisted.
     * @param string $type
     * @param string $functionName , ...
     */
    public function whitelistFunction($type, $functionName)
    {

        if (isset($this->alias[$type])) {

            foreach ($this->alias[$type] as $target) {
                call_user_func_array([$this, "whitelistFunction"], array_merge([$target], func_get_args()));
//                $this->whitelistFunction($target, $functionName);
            }
            return;
        }

        $first = true;
        foreach (func_get_args() as $arg) {
            if ($first) {
                $first = false;
                continue;
            }
            $this->functionWhitelist[$type][] = $arg;
        }


    }


    /**
     * Sets up the type handler for provided type.
     * This should be called for each registered type.
     * @param Server $server The server which is setting-up the handler
     * @param string $type The type currently being set-up
     * @return void
     */
    public function setUp(Server $server, $type)
    {

        if (in_array($type, $this->hasBeenSetUp)) {
            return;
        }
        $this->hasBeenSetUp[] = $type;

        if (isset($this->alias[$type])) {
            foreach ($this->alias[$type] as $target) {
                $this->setUp($server, $target);
            }
            return;
        }
        if (!class_exists($type) && !interface_exists($type)) {
            return;
        }
        $r = new ReflectionClass($type);
        $methods = $r->getMethods();
        $this->functions[$type] = array_map(function (ReflectionMethod $method) {
            return $method->getName();
        }, $methods);

        if (!isset($this->functionWhitelist[$type])) {
            return;
        }

        foreach ($this->functionWhitelist[$type] as $k => $fn) {
            if (in_array($fn, $this->functions[$type]) || (isset($this->customFunctions[$type], $this->customFunctions[$type][$fn]))) {
                continue;
            }
            unset($this->functionWhitelist[$type][$k]);
        }

    }

    /**
     * Lists the types that this handler can handle.
     * @return array An array of strings
     */
    public function listTypes()
    {
        return count($this->typeWhitelist) ? $this->typeWhitelist : $this->types;

    }

    /**
     * @param string $type
     * @return String[]
     */
    public function listFunctions($type)
    {
        if (!$this->hasType($type)) {
            return array();
        }

        if (isset($this->alias[$type])) {
            $result = [];
            foreach ($this->alias[$type] as $target) {
                $l = $this->listFunctions($target);
                $result = array_merge($result, $l);
            }
            return $result;
        }
        if (isset($this->functionWhitelist[$type]) && count($this->functionWhitelist[$type]) > 0) {
            $resultArray = array();

            foreach ($this->functionWhitelist[$type] as $function) {
                if ($this->hasRealFunction($type, $function)) {
                    $resultArray[] = $function;
                }
            }
            if (count($resultArray) > 0) {
                return $resultArray;
            }
        }
        $result = [];

        if (isset($this->functions[$type])) {
            $result = $this->functions[$type];
        }

        if (isset($this->customFunctions[$type])) {
            $result = array_merge($result, array_keys($this->customFunctions[$type]));
        }


        return $result;
    }

    /**
     * Checks if handler can handle. If so handle will be called with same arguments, else next suitable handler will be called.
     * @param string $type
     * @param \ChristianBudde\Part\controller\json\JSONFunction $function
     * @param mixed $instance
     * @return bool
     */
    public function canHandle($type, JSONFunction $function, $instance = null)
    {
        if (isset($this->alias[$type])) {
            $canHandle = false;
            foreach ($this->alias[$type] as $target) {
                $canHandle = $canHandle || $this->canHandle($target, $function, $instance);
            }
            return $canHandle;
        }


        $instance = $instance == null ? $this->object : $instance;


        $name = $function->getName();

        if (!$this->hasFunction($type, $name)) {
            return false;
        }

        $args = $function->getArgs();
        /** @var \ReflectionParameter[] $parameters */
        $parameters = null;
        $this->callPreCallFunctions($type, $instance, $name, $args);

        if (isset($this->customFunctions[$type][$name])) {
            $f = new \ReflectionFunction($this->customFunctions[$type][$name]);
            $parameters = $f->getParameters();
            $args = array_merge([$instance], $args);
        } else if ($instance != null && isset($this->functions[$type]) && in_array($name, $this->functions[$type])) {
            $m = new \ReflectionMethod($instance, $name);
            $parameters = $m->getParameters();
        }
        if ($parameters === null) {
            return false;
        }

        return $this->parametersCheck($args, $parameters);
    }

    /**
     * @param string $type
     * @param \ChristianBudde\Part\controller\json\JSONFunction $function
     * @param mixed $instance
     * @throws \Exception
     * @return mixed
     */
    public function handle($type, JSONFunction $function, $instance = null)
    {
        if (isset($this->alias[$type])) {
            foreach ($this->alias[$type] as $target) {
                if (!$this->canHandle($target, $function, $instance)) {
                    continue;
                }
                return $this->handle($target, $function, $instance);
            }
            return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_NO_SUCH_FUNCTION);

        }


        $instance = $instance == null ? $this->object : $instance;
        $name = $function->getName();
        if (!$this->checkAuth($type, $instance, $name, $function)) {
            return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_UNAUTHORIZED);
        }

        $arguments = $function->getArgs();
        $this->callPreCallFunctions($type, $instance, $name, $arguments);
        if (isset($this->customFunctions[$type][$name])) {
            $result = call_user_func_array($this->customFunctions[$type][$name], array_merge([$instance], $arguments));
        } else {
            if ($instance == null) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_NO_SUCH_FUNCTION);
            }
            $result = call_user_func_array(array($instance, $name), $arguments);
        }
        $this->callPostCallFunctions($type, $instance, $name, $result);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasType($type)
    {
        return in_array($type, $this->listTypes());
    }


    public function hasFunction($type, $function)
    {
        return in_array($function, $this->listFunctions($type));
    }

    private function checkAuth($type, $instance, $name, JSONFunction $function)
    {
        foreach ($this->authFunctions as $f) {
            if (!$f($type, $instance, $name, $function->getArgs())) {
                return false;
            }

        }

        if (isset($this->typeAuthFunctions[$type])) {
            foreach ($this->typeAuthFunctions[$type] as $f) {
                if (!$f($type, $instance, $name, $function->getArgs())) {
                    return false;
                }

            }
        }

        if (isset($this->functionAuthFunctions[$type][$fn = $function->getName()])) {
            foreach ($this->functionAuthFunctions[$type][$fn] as $f) {
                if (!$f($type, $instance, $name, $function->getArgs())) {
                    return false;
                }

            }
        }

        return true;
    }

    private function callPreCallFunctions($type, $instance, $functionName, &$arguments)
    {
        foreach ($this->preCallFunctions as $f) {
            $f($type, $instance, $functionName, $arguments);
        }

        if (isset($this->typePreCallFunctions[$type])) {
            foreach ($this->typePreCallFunctions[$type] as $f) {
                $f($type, $instance, $functionName, $arguments);
            }
        }


        if (isset($this->functionPreCallFunctions[$type][$functionName])) {
            foreach ($this->functionPreCallFunctions[$type][$functionName] as $f) {
                $f($type, $instance, $functionName, $arguments);
            }
        }


    }

    private function callPostCallFunctions($type, $instance, $functionName, &$result)
    {
        foreach ($this->postCallFunctions as $f) {
            $f($type, $instance, $functionName, $result);
        }

        if (isset($this->typePostCallFunctions[$type])) {
            foreach ($this->typePostCallFunctions[$type] as $f) {
                $f($type, $instance, $functionName, $result);
            }
        }


        if (isset($this->functionPostCallFunctions[$type][$functionName])) {
            foreach ($this->functionPostCallFunctions[$type][$functionName] as $f) {
                $f($type, $instance, $functionName, $result);
            }
        }

    }

    private function hasRealFunction($type, $function)
    {
        if (isset($this->functions[$type]) && in_array($function, $this->functions[$type])) {
            return true;
        }
        if (isset($this->customFunctions[$type][$function])) {
            return true;
        }
        return false;
    }

    /**
     * @param string $type
     * @return void
     */
    public function addGetInstanceFunction($type)
    {
        $function = function ($instance) {
            return $instance;
        };
        $this->addFunction($type, 'getInstance', $function);
    }

    /**
     * @param array $functionArgs
     * @param \ReflectionParameter[] $parameters
     * @return bool
     */
    private function parametersCheck(array $functionArgs, array $parameters)
    {


        $numRequiredParameters = 0;
        $lastRequiredFound = false;
        foreach (array_reverse($parameters) as $param) {
            /** @var $param \ReflectionParameter */

            $lastRequiredFound = $lastRequiredFound || !$param->isOptional();
            if ($lastRequiredFound) {
                $numRequiredParameters++;
            }
        }

        if ($numRequiredParameters > count($functionArgs)) {
            return false;
        }

        foreach ($parameters as $k => $param) {
            if ($param->isArray()) {
                if (isset($functionArgs[$k]) && !is_array($functionArgs[$k])) {
                    return false;
                }
            }
            if ($c = $param->getClass()) {
                if (isset($functionArgs[$k])) {

                    if (!is_a($functionArgs[$k], $c->getName())) {
                        return false;
                    } else {
                        continue;
                    }
                }
                if (!$param->isDefaultValueAvailable()) {
                    return false;
                }


            }

        }

        return true;

    }

    /**
     * Adds an alias.
     * If the alias already exists the types are merged.
     * @param string $alias
     * @param array $target
     */
    public function addAlias($alias, array $target){
        $this->alias[$alias] = isset($this->alias[$alias])?array_merge($this->alias[$alias], $target):$target;

        if(in_array($alias, $this->types)){
            return;
        }
        $this->types[] = $alias;

    }
}