<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:52 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NamedArrayEntryImpl implements NamedArrayEntry {

    private $name;
    private $value;

    function __construct(Scalar $name, ScalarArrayProgram $value)
    {
        $this->name = $name;
        $this->value = $value;
    }


    /**
     * @return Scalar
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
    public function toArray()
    {
        return [$this->getName()->getValue()=>$this->getValue()->toJSON()];
    }
}