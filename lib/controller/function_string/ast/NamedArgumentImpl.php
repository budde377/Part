<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:12 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NamedArgumentImpl implements NamedArgument{
    private $name;
    private $value;

    function __construct(NameNotStartingWithUnderscore $name, ScalarArrayProgram $value)
    {
        $this->name = $name;
        $this->value = $value;
    }


    /**
     * @return NameNotStartingWithUnderscore
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ScalarArrayProgram
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function toArgumentArray()
    {
        return [$this->getName()->getValue()=>$this->getValue()->toJSON()];
    }
}