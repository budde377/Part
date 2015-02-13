<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:17 PM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


use ChristianBudde\Part\controller\json\JSONFunction;
use ChristianBudde\Part\controller\json\Target;

interface FunctionChain extends CompositeFunction{


    /**
     * @param Target $target
     * @return JSONFunction
     */
    public function toJSONFunction(Target $target);

} 