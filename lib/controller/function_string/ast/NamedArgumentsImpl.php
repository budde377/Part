<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:13 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NamedArgumentsImpl implements NamedArguments{
    private $name;
    private $value;
    private $namedArgument;

    function __construct(NameNotStartingWithUnderscore $name, ScalarArrayProgram $value, NamedArgument $namedArgument)
    {
        $this->name = $name;
        $this->value = $value;
        $this->namedArgument = $namedArgument;
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
     * @return NamedArgument
     */
    public function getNamedArgument()
    {
        return $this->namedArgument;
    }
}