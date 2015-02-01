<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:30 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class ArrayAccessFunctionImpl implements ArrayAccessFunction{
    private $argument;

    function __construct(ScalarArrayProgram $argument)
    {
        $this->argument = $argument;
    }


    /**
     * @return ScalarArrayProgram
     */
    public function getArgument()
    {
        return $this->argument;
    }
}