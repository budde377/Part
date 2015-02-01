<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:09 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class CompositeFunctionsImpl implements CompositeFunctions{
    private $functionChain;
    private $compositeFunction;

    function __construct(FunctionChain $functionChain, CompositeFunction $compositeFunction)
    {
        $this->functionChain = $functionChain;
        $this->compositeFunction = $compositeFunction;
    }

    /**
     * @return FunctionChain
     */
    public function getFunctionChain()
    {
        return $this->functionChain;
    }

    /**
     * @return CompositeFunction
     */
    public function getCompositeFunction()
    {
        return $this->compositeFunction;
    }
}