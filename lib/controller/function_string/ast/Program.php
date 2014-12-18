<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:08 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;

use ChristianBudde\cbweb\controller\json\Program as JProgram;

abstract class Program implements ScalarArrayProgram{

    /**
     * @return JProgram
     */
    abstract public function toJSONProgram();

    /**
     * @param callable $programComputer
     * @return mixed
     */
    public function compute(callable $programComputer)
    {
        return $programComputer($this);
    }


}