<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:17 AM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


interface FunctionChains extends FunctionChain{

    /**
     * @return FFunction
     */
    public function getFunction();

    /**
     * @return FunctionChain
     */
    public function getFunctionChain();

}