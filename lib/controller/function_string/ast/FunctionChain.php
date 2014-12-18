<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:17 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


interface FunctionChain {

    /**
     * @param Target $target
     * @return FunctionCallImpl
     */
    public function toFunctionCall(Target $target);
} 