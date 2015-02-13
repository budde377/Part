<?php
namespace ChristianBudde\Part\controller\json;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 12:53 PM
 */
class JSONFunctionImpl extends ProgramImpl implements JSONFunction
{

    private $name;

    private $args;

    /**
     * @param string $name
     * @param Target $target
     * @param array $args
     */
    function __construct($name, Target $target, array $args = [])
    {
        parent::__construct($target);
        $this->name = $name;
        $this->args = $args;
    }


    /**
     * @return array
     */
    public function getAsArray()
    {
        return array(
            'type' => 'function',
            'name' => $this->getName(),
            'target' => $this->target,
            'arguments' => $this->args,
            'id' => $this->id);
    }

    /**
     * Will return a numerical array of arguments
     * @return Array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Will return argument at index given
     * @param $num
     * @return mixed
     */
    public function getArg($num)
    {
        return isset($this->args[$num]) ? $this->args[$num] : null;
    }

    /**
     * Will return the name of the function as a String
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }


}