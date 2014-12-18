<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:16 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class CompositeChainCompositeFunctionImpl implements CompositeFunction
{

    private $compositeFunction;
    private $functionChain;

    function __construct(CompositeFunction $compositeFunction, FunctionChain $functionChain)
    {
        $this->compositeFunction = $compositeFunction;
        $this->functionChain = $functionChain;
    }

    /**
     * @return CompositeFunction
     */
    public function getCompositeFunction()
    {
        return $this->compositeFunction;
    }

    /**
     * @return FunctionChain
     */
    public function getFunctionChain()
    {
        return $this->functionChain;
    }


    /**
     * @param Target $target
     * @return FunctionCallImpl[]
     */
    public function toFunctionCalls(Target $target)
    {
        return array_merge($this->compositeFunction->toFunctionCalls($target), [$this->functionChain->toFunctionCall($target)]);
    }
}