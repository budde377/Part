<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:24 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class ArgumentsImpl implements  ArgumentList{

    /** @var  ScalarArrayProgram */
    private $value;
    /** @var  ArgumentList */
    private $argumentList;

    function __construct(ScalarArrayProgram $value, ArgumentList $argumentList)
    {
        $this->value = $value;
        $this->argumentList = $argumentList;
    }

    /**
     * @return ArgumentList
     */
    public function getArgumentList()
    {
        return $this->argumentList;
    }

    /**
     * @return ScalarArrayProgram
     */
    public function getValue()
    {
        return $this->value;
    }




} 