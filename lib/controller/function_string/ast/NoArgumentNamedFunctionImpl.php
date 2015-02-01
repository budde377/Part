<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:45 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\controller\json\CompositeFunction as JCompositeFunction;
use ChristianBudde\cbweb\controller\json\CompositeFunctionImpl as JCompositeFunctionImpl;
use ChristianBudde\cbweb\controller\json\JSONFunction;
use ChristianBudde\cbweb\controller\json\JSONFunctionImpl;
use ChristianBudde\cbweb\controller\json\Target;

class NoArgumentNamedFunctionImpl implements  NoArgumentNamedFunction{

    private  $name;

    function __construct(Name $name)
    {
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
        return new JSONFunctionImpl($this->getName()->getValue(), $target);
    }
}