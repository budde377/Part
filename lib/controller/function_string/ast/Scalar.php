<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:28 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


abstract class Scalar implements ScalarArrayProgram{

    abstract public function getValue();


    /**
     * @param callable $programComputer
     * @return mixed
     */
    public function compute(callable $programComputer)
    {
        return $this->getValue();
    }


} 