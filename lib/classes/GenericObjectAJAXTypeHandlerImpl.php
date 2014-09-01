<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/31/14
 * Time: 7:32 PM
 */

class GenericObjectAJAXTypeHandlerImpl implements AJAXTypeHandler{


    // Auth. function : f ( typeString, instance, functionName, arguments) => bool

    /**
     * @param array $whiteList
     * @param array $blackList
     * @param callable $authFunction
     * @return callable
     */
    public function createAuthFunction($whiteList = array(), $blackList = array(), Callable $authFunction = null){

    }

    /**
     * @param mixed $object
     * @param callable $authFunction
     * @param array $type
     */
    public function registerObject($object, Callable $authFunction, $type = array()){

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
        // TODO: Implement setUp() method.
    }

    /**
     * Lists the types that this handler can handle.
     * @return array An array of strings
     */
    public function listTypes()
    {
        // TODO: Implement listTypes() method.
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
        // TODO: Implement canHandle() method.
    }

    /**
     * @param string $type
     * @param JSONFunction $function
     * @param mixed $instance
     * @return mixed
     */
    public function handle($type, JSONFunction $function, $instance = null)
    {
        // TODO: Implement handle() method.
    }
}