<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:19 AM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


interface ArrayAccessFunction extends FFunction{

    /**
     * @return ScalarArrayProgram
     */
    public function getArgument();

}