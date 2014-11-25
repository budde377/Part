<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:40 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


interface ArgumentNamedFunction extends FFunction{

    /**
     * @return NameImpl
     */
    public function getName();
} 