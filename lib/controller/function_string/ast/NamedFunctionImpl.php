<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:31 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\controller\json\CompositeFunction as JCompositeFunction;
use ChristianBudde\cbweb\controller\json\CompositeFunctionImpl as JCompositeFunctionImpl;
use ChristianBudde\cbweb\controller\json\JSONFunction;
use ChristianBudde\cbweb\controller\json\JSONFunctionImpl;
use ChristianBudde\cbweb\controller\json\Target;

class NamedFunctionImpl implements NamedFunction{
    private $argumentList;
    private $name;

    function __construct(Name $name, Argument $argumentList)
    {
        $this->argumentList = $argumentList;
        $this->name = $name;
    }


    /**
     * @return Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Argument
     */
    public function getArgument()
    {
        return $this->argumentList;
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
        return new JSONFunctionImpl($this->getName()->getValue(), $target, $this->getArgument()->toArgumentArray());
    }
}