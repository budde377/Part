<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:35 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class FunctionChainsImpl implements FunctionChains{
    private $function;
    private $functionChain;

    function __construct(FFunction $function, FunctionChain $functionChain)
    {
        $this->function = $function;
        $this->functionChain = $functionChain;
    }


    /**
     * @return FFunction
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @return FunctionChain
     */
    public function getFunctionChain()
    {
        return $this->functionChain;
    }
}