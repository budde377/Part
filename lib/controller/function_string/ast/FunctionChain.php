<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:17 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\controller\json\JSONFunction;
use ChristianBudde\cbweb\controller\json\Target;

interface FunctionChain extends CompositeFunction{


    /**
     * @param Target $target
     * @return JSONFunction
     */
    public function toJSONFunction(Target $target);

} 