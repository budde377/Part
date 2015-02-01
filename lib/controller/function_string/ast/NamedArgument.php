<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:25 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


interface NamedArgument extends Argument{



    /**
     * @return NameNotStartingWithUnderscore
     */
    public function getName();

    /**
     * @return ScalarArrayProgram
     */
    public function getValue();


} 