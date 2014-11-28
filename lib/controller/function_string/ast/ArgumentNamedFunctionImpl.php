<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:21 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class ArgumentNamedFunctionImpl extends ArgumentNamedFunction{

    /** @var  NameImpl */
    private $name;

    /** @var  ArgumentList */
    private $argumentList;

    function __construct(NameImpl $name, ArgumentList $argumentList)
    {
        $this->name = $name;
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
        return $this->argumentList->toArgumentList();
    }

    /**
     * @return ArgumentImpl[]
     */
    public function generatePositionalArgumentList()
    {
        $r = [];
        foreach($this->generateArgumentList() as $arg){
            if($arg instanceof NamedArgumentImpl){
                return $r;
            }
            $r[] = $arg;
        }
        return $r;

    }

    /**
     * @return NamedArgumentImpl[]
     */
    public function generateNamedArgumentList()
    {
        $r = [];
        foreach($this->generateArgumentList() as $arg){
            if($arg instanceof ScalarArrayProgram){
                continue;
            }
            $r[] = $arg;
        }
        return $r;
    }
}