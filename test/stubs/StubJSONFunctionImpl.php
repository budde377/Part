<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 13:46
 * To change this template use File | Settings | File Templates.
 */
class StubJSONFunctionImpl implements JSONFunction
{

    private $response;
    private $args;
    private $name;
    private $id;
    function __construct($name, JSONResponse $response,$id = null,array $args = array())
    {
        $this->response = $response;
        $this->args = $args;
        $this->name = $name;
        $this->id = $id;
    }
    /**
     * Will return a numerical array of arguments as strings
     * @return Array
     */
    public function getArgs()
    {
        return $this->args;
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
        return $this->response;
    }

}
