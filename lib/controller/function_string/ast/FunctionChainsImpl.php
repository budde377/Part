<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:35 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\controller\json\CompositeFunction as JCompositeFunction;
use ChristianBudde\cbweb\controller\json\JSONFunction;
use ChristianBudde\cbweb\controller\json\Target;
use ChristianBudde\cbweb\test\JSONCompositeFunctionImplTest;

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

    /**
     * @param Target $target
     * @return JCompositeFunction
     */
    public function toJSONCompositeFunction(Target $target)
    {
        return new JSONCompositeFunctionImplTest($target, [$this->toJSONFunction($target)]);
    }

    /**
     * @param Target $target
     * @return JSONFunction
     */
    public function toJSONFunction(Target $target)
    {
        return $this->getFunctionChain()->toJSONFunction($this->getFunction()->toJSONFunction($target));
    }
}