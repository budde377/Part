<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:12 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;

use ChristianBudde\cbweb\controller\json\Target;
use ChristianBudde\cbweb\controller\json\CompositeFunction as JCompositeFunction;

interface CompositeFunction {

    /**
     * @param Target $target
     * @return JCompositeFunction
     */
    public function toJSONCompositeFunction(Target $target);

} 