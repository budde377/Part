<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 16:30
 * To change this template use File | Settings | File Templates.
 */
class JSONFunctionImpl implements JSONFunction
{
    private $callFunction;
    private $args;
    private $name;

    function __construct($name, callable $callFunction, array $args = array())
    {
        $this->args = $args;
        $this->callFunction = $callFunction;
        $this->name = $name;
    }


    /**
     * Will return a numerical array of arguments as strings
     * @return Array
     */
    public function getArgs()
    {
        $returnArgs = array();
        foreach($this->args as $arg){
            if(is_string($arg)){
                $returnArgs[] = $arg;
            }
        }
        return $returnArgs;
    }

    /**
     * Will return the name of the function as a String
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $args Associative array containing arg name and value
     * @return JSONResponse
     */
    public function call(array $args = array())
    {
        $call = $this->callFunction;
        return call_user_func_array($call,$args);
    }
}
