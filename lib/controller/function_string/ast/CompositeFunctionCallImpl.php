<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:12 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\controller\json\CompositeFunction as JCompositeFunction;
use ChristianBudde\cbweb\controller\json\CompositeFunctionImpl;

class CompositeFunctionCallImpl extends Program
{

    private $target;
    private $compositeFunction;

    function __construct(Target $target, CompositeFunction $compositeFunction)
    {
        $this->target = $target;
        $this->compositeFunction = $compositeFunction;
    }

    /**
     * @return CompositeFunction
     */
    public function getCompositeFunction()
    {
        return $this->compositeFunction;
    }

    /**
     * @return Target
     */
    public function getTarget()
    {
        return $this->target;
    }


    /**
     * @return JCompositeFunction
     */
    public function toJSONProgram()
    {
        $f = new CompositeFunctionImpl($this->target->toJSONTarget(),
            array_map(function (FunctionCallImpl $callImpl) {
                return $callImpl->toJSONProgram();
            }, $this->compositeFunction->toFunctionCalls($this->target)));
        return $f;
    }


}