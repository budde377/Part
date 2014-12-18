<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:20 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NoArgumentNamedFunctionImpl extends ArgumentNamedFunction{

    /** @var  NameImpl */
    private $name;

    function __construct(NameImpl $name)
    {
        $this->name = $name;
    }

    /**
     * @return NameImpl
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @return ArgumentList[]
     */
    public function generateArgumentList()
    {
        return [];
    }

    /**
     * @return ScalarArrayProgram[]
     */
    public function generatePositionalArgumentList()
    {
        return [];
    }

    /**
     * @return NamedArgumentImpl[]
     */
    public function generateNamedArgumentList()
    {
        return [];
    }
}