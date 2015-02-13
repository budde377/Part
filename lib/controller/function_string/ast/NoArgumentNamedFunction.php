<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:44 AM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


interface NoArgumentNamedFunction extends FFunction{

    /**
     * @return Name
     */
    public function getName();
}