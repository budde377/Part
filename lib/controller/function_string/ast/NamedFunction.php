<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:19 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


interface NamedFunction extends FFunction{

    /**
     * @return Name
     */
    public function getName();

    /**
     * @return Argument
     */
    public function getArgumentList();

}