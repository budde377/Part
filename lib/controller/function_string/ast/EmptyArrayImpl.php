<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/28/14
 * Time: 5:00 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class EmptyArrayImpl implements AArray{

    /**
     * @param callable $programComputer
     * @return mixed
     */
    public function compute(callable $programComputer)
    {
        return [];
    }

    public function toArray()
    {
        return [];
    }
}