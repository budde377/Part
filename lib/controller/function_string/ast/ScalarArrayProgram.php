<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:26 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


interface ScalarArrayProgram{

    /**
     * @param callable $programComputer
     * @return mixed
     */
    public function compute(callable $programComputer);

} 