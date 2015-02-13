<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:30 AM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


use ChristianBudde\Part\controller\json\CompositeFunction as JCompositeFunction;
use ChristianBudde\Part\controller\json\CompositeFunctionImpl as JCompositeFunctionImpl;
use ChristianBudde\Part\controller\json\JSONFunction;
use ChristianBudde\Part\controller\json\JSONFunctionImpl;
use ChristianBudde\Part\controller\json\Target;

class ArrayAccessFunctionImpl implements ArrayAccessFunction{
    private $argument;

    function __construct(ScalarArrayProgram $argument)
    {
        $this->argument = $argument;
    }


    /**
     * @return ScalarArrayProgram
     */
    public function getArgument()
    {
        return $this->argument;
    }

    /**
     * @param Target $target
     * @return JCompositeFunction
     */
    public function toJSONCompositeFunction(Target $target)
    {
        return new JCompositeFunctionImpl($target, [$this->toJSONFunction($target)]);
    }

    /**
     * @param Target $target
     * @return JSONFunction
     */
    public function toJSONFunction(Target $target)
    {
        return new JSONFunctionImpl('arrayAccess', $target, [$this->getArgument()->toJSON()]);
    }
}