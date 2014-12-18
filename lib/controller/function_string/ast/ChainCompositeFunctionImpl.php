<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:15 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;



class ChainCompositeFunctionImpl implements CompositeFunction{

    private $functionChain;

    function __construct(FunctionChain $functionChain)
    {
        $this->functionChain = $functionChain;
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
        return [$this->functionChain->toFunctionCall($target)];
    }
}