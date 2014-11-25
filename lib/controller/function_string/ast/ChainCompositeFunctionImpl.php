<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:15 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\controller\function_string\CompositeFunction;

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




} 