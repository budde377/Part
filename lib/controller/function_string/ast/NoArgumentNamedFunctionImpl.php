<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:45 AM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


use ChristianBudde\Part\controller\json\CompositeFunction as JCompositeFunction;
use ChristianBudde\Part\controller\json\CompositeFunctionImpl as JCompositeFunctionImpl;
use ChristianBudde\Part\controller\json\JSONFunction;
use ChristianBudde\Part\controller\json\JSONFunctionImpl;
use ChristianBudde\Part\controller\json\Target;

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