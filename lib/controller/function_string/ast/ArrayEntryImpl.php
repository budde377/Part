<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/28/14
 * Time: 12:15 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class ArrayEntryImpl implements PositionalArrayEntry {
    /** @var  ScalarArrayProgram */
    private $value;

    function __construct(ScalarArrayProgram $value)
    {
        $this->value = $value;
    }

    /**
     * @return ScalarArrayProgram
     */
    public function getValue()
    {
        return $this->value;
    }



    /**
     * @return ScalarArrayProgram[]
     */
    public function toArray()
    {
        return [$this->value];
    }
}