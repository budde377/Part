<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:24 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class ArgumentsImpl implements  ArgumentList{

    /** @var  ArgumentImpl  */
    private $argument;
    /** @var  ArgumentList */
    private $argumentList;

    function __construct(ArgumentImpl $argument, ArgumentList $argumentList)
    {
        $this->argument = $argument;
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
     * @return ArgumentImpl
     */
    public function getArgument()
    {
        return $this->argument;
    }


    /**
     * @return ArgumentList[]
     */
    public function toArgumentList()
    {
        return array_merge($this->getArgument()->toArgumentList(), $this->getArgumentList()->toArgumentList());
    }
}