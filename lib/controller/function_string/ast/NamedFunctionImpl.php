<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:31 AM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


use ChristianBudde\Part\controller\json\CompositeFunction as JCompositeFunction;
use ChristianBudde\Part\controller\json\CompositeFunctionImpl as JCompositeFunctionImpl;
use ChristianBudde\Part\controller\json\JSONFunction;
use ChristianBudde\Part\controller\json\JSONFunctionImpl;
use ChristianBudde\Part\controller\json\Target;

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