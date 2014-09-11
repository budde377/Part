<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 13:46
 * To change this template use File | Settings | File Templates.
 */
class StubJSONFunctionImpl implements ChristianBudde\cbweb\JSONFunction
{

    private $response;
    private $args;
    private $name;
    private $id;
    function __construct($name, ChristianBudde\cbweb\JSONResponse $response,$id = null,array $args = array())
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
     * @return ChristianBudde\cbweb\JSONResponse
     */
    public function call(array $args = array())
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getAsJSONString()
    {
    }

    /**
     * @return array
     */
    public function getAsArray()
    {
    }

    /**
     * @return ChristianBudde\cbweb\JSONTarget
     */
    public function getTarget()
    {
    }

    /**
     * Will return argument at index given
     * @param $num
     * @return mixed
     */
    public function getArg($num)
    {
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
    }

    /**
     * @param ChristianBudde\cbweb\JSONTarget $target
     * @return void
     */
    public function setTarget(ChristianBudde\cbweb\JSONTarget $target)
    {
    }

    /**
     * Will set an argument with value
     * @param int $num
     * @param mixed $value
     * @return void
     */
    public function setArg($num, $value)
    {
    }

    /**
     * Clears arguments
     * @return void
     */
    public function clearArguments()
    {
    }

    public function getId()
    {
    }

    public function setId($id)
    {
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
    }

    /**
     * Sets the root target, i.e. calls recursively on target until target is not a function.
     * @param ChristianBudde\cbweb\JSONTarget $target
     * @return void
     */
    public function setRootTarget(ChristianBudde\cbweb\JSONTarget $target)
    {
    }
}
