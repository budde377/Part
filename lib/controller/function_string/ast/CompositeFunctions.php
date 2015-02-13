<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:11 AM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


interface CompositeFunctions extends CompositeFunction{

    /**
     * @return FunctionChain
     */
    public function getFunctionChain();
    /**
     * @return CompositeFunction
     */
    public function getCompositeFunction();
    
}