<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:09 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\controller\json\CompositeFunction as JCompositeFunction;
use ChristianBudde\cbweb\controller\json\CompositeFunctionImpl as JCompositeFunctionImpl;
use ChristianBudde\cbweb\controller\json\Target;

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

    /**
     * @param Target $target
     * @return JCompositeFunction
     */
    public function toJSONCompositeFunction(Target $target)
    {
        $f = $this->functionChain->toJSONFunction($target);
        $cf = $this->compositeFunction->toJSONCompositeFunction($target);
        return new JCompositeFunctionImpl($target, array_merge([$f],$cf->listFunctions()));
    }
}