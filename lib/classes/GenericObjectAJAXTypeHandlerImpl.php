<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/31/14
 * Time: 7:32 PM
 */

class GenericObjectAJAXTypeHandlerImpl implements AJAXTypeHandler{


    private $object;
    private $types;
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


    function __construct($object)
    {
        $this->object = $object;
        $reflection = new ReflectionClass($object);
        $this->types = $reflection->getInterfaceNames();
    }


    /**
     * Adds an auth function of type: f(type, instance, function_name, arguments) => bool
     * @param callable $function
     */
    public function addAuthFunction(callable $function){
        $this->authFunctions[] = $function;
    }

    /**
     * Adds an auth function of type: f(type, instance, function_name, arguments) => bool
     * @param string $type
     * @param string $functionName
     * @param callable $function
     */
    public function addFunctionAuthFunction($type, $functionName, callable $function){
        $this->functionAuthFunctions[$type][$functionName] = $function;
    }

    /**
     * Adds an auth function of type: f(type, instance, function_name, arguments) => bool
     * @param string $type
     * @param callable $function
     */
    public function addTypeAuthFunction($type, callable $function){
        $this->typeAuthFunctions[$type][] = $function;
    }

    /**
     * Adds a function of type: f(instance, arguments ... ) => mixed
     * @param string $type
     * @param string $name
     * @param callable $function
     */
    public function addFunction($type, $name, callable $function){
        $this->customFunctions[$type][$name] = $function;
    }

    /**
     * If added function will be called before the function.
     * The function should be of type : f(instance, &arguments) => void
     * @param callable $function
     */
    public function addPreCallFunction(callable $function){
        $this->preCallFunctions[] = $function;
    }

    /**
     * If added function will be called after the function.
     * The function should be of type : f(instance, &result) => void
     * @param callable $function
     */
    public function addPostCallFunction( callable $function){
        $this->postCallFunctions[] = $function;
    }

    /**
     * If added function will be called before the function.
     * The function should be of type : f(instance, &arguments) => void
     * @param $type
     * @param callable $function
     */
    public function addTypePreCallFunction($type, callable $function){
        $this->typePreCallFunctions[$type][] = $function;
    }

    /**
     * If added function will be called after the function.
     * The function should be of type : f(instance, &result) => void
     * @param $type
     * @param callable $function
     */
    public function addTypePostCallFunction($type, callable $function){
        $this->typePostCallFunctions[$type][] =$function;
    }

    /**
     * If added function will be called before the function.
     * The function should be of type : f(instance, &arguments) => void
     * @param $type
     * @param $name
     * @param callable $function
     */
    public function addFunctionPreCallFunction($type, $name, callable $function){
        $this->functionPreCallFunctions[$type][$name][] = $function;
    }

    /**
     * If added function will be called after the function.
     * The function should be of type : f(instance, &result) => void
     * @param $type
     * @param $name
     * @param callable $function
     */
    public function addFunctionPostCallFunction($type, $name, callable $function){
        $this->functionPostCallFunctions[$type][$name][] = $function;
    }

    /**
     * Whitelists a type, if no type is whitelisted; all found types are whitelisted.
     * @param string $type
     */
    public function whitelistType($type){
        if(!$this->hasType($type)){
            return;
        }
        $this->typeWhitelist[] = $type;
    }

    /**
     * Whitelists a function, if no function is whitelisted; all found types are whitelisted.
     * @param string $type
     * @param string $functionName
     */
    public function whitelistFunction($type, $functionName){

        if(isset($this->functions[$type]) && !in_array($functionName, $this->functions[$type])){
            return;
        }

        $this->functionWhitelist[$type][] = $functionName;

    }


    /**
     * Sets up the type handler for provided type.
     * This should be called for each registered type.
     * @param AJAXServer $server The server which is setting-up the handler
     * @param string $type The type currently being set-up
     * @return void
     */
    public function setUp(AJAXServer $server, $type)
    {
        $r = new ReflectionClass($type);
        $methods = $r->getMethods();
        $this->functions[$type] = array_map(function(ReflectionMethod $method){
            return $method->getName();
        }, $methods);

        if(!isset($this->functionWhitelist[$type])){
            return;
        }

        foreach($this->functionWhitelist[$type] as $k => $fn){
            if(in_array($fn, $this->functions[$type])){
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
        return count($this->typeWhitelist)?$this->typeWhitelist:$this->types;

    }

    /**
     * @param string $type
     * @return array
     */
    public function listFunctions($type){
        if(!$this->hasType($type)){
            return array();
        }

        if(!isset($this->functions[$type])){
            return array();
        }

        if(isset($this->functionWhitelist[$type]) && count($this->functionWhitelist[$type]) > 0 ){
            return $this->functionWhitelist[$type];
        }

        $customFunctions = isset($this->customFunctions[$type])?array_keys($this->customFunctions[$type]):[];

        return array_merge($this->functions[$type], $customFunctions);
    }

    /**
     * Checks if handler can handle. If so handle will be called with same arguments, else next suitable handler will be called.
     * @param string $type
     * @param JSONFunction $function
     * @param mixed $instance
     * @return bool
     */
    public function canHandle($type, JSONFunction $function, $instance = null)
    {
        return $this->hasFunction($type, $function->getName());
    }

    /**
     * @param string $type
     * @param JSONFunction $function
     * @param mixed $instance
     * @return mixed
     */
    public function handle($type, JSONFunction $function, $instance = null)
    {

        $instance = $instance == null?$this->object:$instance;
        $name = $function->getName();
        if(!$this->checkAuth($type, $instance, $name, $function)){
            return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
        }

        $arguments = $function->getArgs();
        $this->callPreCallFunctions($type, $instance, $name, $arguments);
        if(isset($this->customFunctions[$type][$name])){
            $result = call_user_func_array($this->customFunctions[$type][$name],array_merge([$instance],$arguments));
        } else {
            $result = call_user_func_array(array($instance, $name), $arguments);
        }
        $this->callPostCallFunctions($type, $instance, $name, $result);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getObject(){
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


    public function hasFunction($type, $function){
        return in_array($function, $this->listFunctions($type));
    }

    private function checkAuth($type, $instance, $name, JSONFunction $function)
    {
        foreach($this->authFunctions as $f){
            if(!$f($type, $instance, $name, $function->getArgs())){
                return false;
            }

        }

        if(isset($this->typeAuthFunctions[$type])){
            foreach($this->typeAuthFunctions[$type] as $f){
                if(!$f($type, $instance, $name, $function->getArgs())){
                    return false;
                }

            }
        }

        if(isset($this->functionAuthFunctions[$type][$function->getName()])){
            foreach($this->functionAuthFunctions[$type] as $f){
                if(!$f($type, $instance, $name, $function->getArgs())){
                    return false;
                }

            }
        }

        return true;
    }

    private function callPreCallFunctions($type, $instance, $functionName, &$arguments)
    {
        foreach($this->preCallFunctions as $f){
            $f($type, $instance, $functionName, $arguments);
        }

        if(isset($this->typePreCallFunctions[$type])){
            foreach($this->typePreCallFunctions[$type] as $f){
                $f($type, $instance, $functionName, $arguments);
            }
        }


        if(isset($this->functionPreCallFunctions[$type][$functionName])){
            foreach($this->functionPreCallFunctions[$type][$functionName] as $f){
                $f($type, $instance, $functionName, $arguments);
            }
        }


    }

    private function callPostCallFunctions($type, $instance, $functionName, &$result)
    {
        foreach($this->postCallFunctions as $f){
            $f($type, $instance, $functionName, $result);
        }

        if(isset($this->typePostCallFunctions[$type])){
            foreach($this->typePostCallFunctions[$type] as $f){
                $f($type, $instance, $functionName, $result);
            }
        }


        if(isset($this->functionPostCallFunctions[$type][$functionName])){
            foreach($this->functionPostCallFunctions[$type][$functionName] as $f){
                $f($type, $instance, $functionName, $result);
            }
        }

    }
}