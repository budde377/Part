<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:18 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class FunctionChainsImpl implements FunctionChain{

    private $functionChain;
    private $function;

    function __construct(FunctionChain $functionChain, FFunction $function)
    {
        $this->functionChain = $functionChain;
        $this->function = $function;
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