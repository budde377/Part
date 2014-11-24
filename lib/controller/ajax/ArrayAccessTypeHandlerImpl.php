<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/6/14
 * Time: 6:07 PM
 */
namespace ChristianBudde\cbweb\controller\ajax;
use ChristianBudde\cbweb\controller\json\JSONFunction;

class ArrayAccessTypeHandlerImpl implements TypeHandler{
    public $arrays;

    /**
     * Sets up the type handler for provided type.
     * This should be called for each registered type.
     * @param Server $server The server which is setting-up the handler
     * @param string $type The type currently being set-up
     * @return void
     */
    public function setUp(Server $server, $type)
    {

    }

    /**
     * Lists the types that this handler can handle.
     * @return string[] An array of strings
     */
    public function listTypes()
    {
        return array_merge(["array"],array_keys($this->arrays));
    }

    /**
     * Checks if handler can handle. If so handle will be called with same arguments, else next suitable handler will be called.
     * @param string $type
     * @param \ChristianBudde\cbweb\controller\json\JSONFunction $function
     * @param mixed $instance
     * @return bool
     */
    public function canHandle($type, JSONFunction $function, $instance = null)
    {
        return $function->getName() == "arrayAccess" && (isset($this->arrays[$type]) || ($type == "array" && is_array($instance)));
    }

    /**
     * @param string $type
     * @param JSONFunction $function
     * @param mixed $instance
     * @return mixed
     */
    public function handle($type, JSONFunction $function, $instance = null)
    {
        if($type == "array"){
            return !isset($instance[$function->getArg(0)])?null:$instance[$function->getArg(0)];
        }

        return !isset($this->arrays[$type][$function->getArg(0)])?null:$this->arrays[$type][$function->getArg(0)];
    }

    /**
     * Check if it has type
     * @param string $type
     * @return bool
     */
    public function hasType($type)
    {
        return $type == "array" || isset($this->arrays[$type]);
    }

    /**
     * @param string $type
     * @param array $array
     */
    public function addArray($type, array $array)
    {
        $this->arrays[$type] = $array;
    }
}