<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:09 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


interface NamedArguments extends NamedArgument{

    /**
     * @return NamedArgument
     */
    public function getNamedArgument();

}