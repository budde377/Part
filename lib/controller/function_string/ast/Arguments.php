<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:41 AM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


interface Arguments extends Argument{

    /**
     * @return ScalarArrayProgram
     */
    public function getValue();
    /**
     * @return Argument
     */
    public function getArgument();

}