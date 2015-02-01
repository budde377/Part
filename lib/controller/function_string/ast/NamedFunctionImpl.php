<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:31 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NamedFunctionImpl implements NamedFunction{
    private $argumentList;
    private $name;

    function __construct(Name $name, Argument $argumentList)
    {
        $this->argumentList = $argumentList;
        $this->name = $name;
    }


    /**
     * @return Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Argument
     */
    public function getArgumentList()
    {
        return $this->argumentList;
    }

}