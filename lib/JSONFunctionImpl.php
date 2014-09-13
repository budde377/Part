<?php
namespace ChristianBudde\cbweb;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 12:53 PM
 */
class JSONFunctionImpl extends JSONProgramImpl implements JSONFunction
{

    private $name;

    private $args = array();

    private $size = 0;

    /**
     * @param string $name
     * @param JSONTarget $target
     */
    function __construct($name, JSONTarget $target)
    {
        parent::__construct($target);
        $this->name = $name;
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

    /**
     * Will set an argument with value
     * @param int $num
     * @param mixed $value
     * @return void
     */
    public function setArg($num, $value)
    {

        if (!is_numeric($num)) {
            return;
        }
        if (!$this->validValue($value)) {
            return;
        }

        if ($this->size < $num) {
            for ($i = $this->size; $i < $num; $i++) {
                $this->args[$i] = null;
            }
        }
        $this->size = $num + 1;
        $this->args[$num] = $value;
    }

    /**
     * Clears arguments
     * @return void
     */
    public function clearArguments()
    {
        $this->size = 0;
        $this->args = array();
    }


    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Sets the root target, i.e. calls recursively on target until target is not a function.
     * @param JSONTarget $target
     * @return void
     */
    public function setRootTarget(JSONTarget $target)
    {
        if($this->target instanceof JSONFunction){
            $this->target->setTarget($target);
        } else {
            $this->setTarget($target);
        }
    }
}