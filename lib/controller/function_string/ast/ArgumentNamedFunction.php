<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:40 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


abstract class ArgumentNamedFunction extends FFunction{

    /**
     * @return NameImpl
     */
    abstract  public function getName();
} 