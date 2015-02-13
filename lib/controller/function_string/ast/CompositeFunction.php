<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:12 PM
 */

namespace ChristianBudde\Part\controller\function_string\ast;

use ChristianBudde\Part\controller\json\CompositeFunction as JCompositeFunction;
use ChristianBudde\Part\controller\json\Target;

interface CompositeFunction {

    /**
     * @param Target $target
     * @return JCompositeFunction
     */
    public function toJSONCompositeFunction(Target $target);

} 