<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:21 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class ArrayAccessFunctionImpl extends  FFunction{

    /** @var  ScalarArrayProgram */
    private $key;

    function __construct(ScalarArrayProgram $key)
    {
        $this->key = $key;
    }

    /**
     * @return ScalarArrayProgram
     */
    public function getKey()
    {
        return $this->key;
    }


    /**
     * @return ArgumentList[]
     */
    public function generateArgumentList()
    {
        return $this->generatePositionalArgumentList();
    }

    /**
     * @return ScalarArrayProgram[]
     */
    public function generatePositionalArgumentList()
    {
        return [new ArgumentImpl($this->getKey())];
    }

    /**
     * @return NamedArgumentImpl[]
     */
    public function generateNamedArgumentList()
    {
        return [];
    }
}