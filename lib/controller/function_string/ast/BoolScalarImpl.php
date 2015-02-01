<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 10:21 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class BoolScalarImpl implements Scalar{
    private $value;

    function __construct($value)
    {
        $this->value = strtolower($value) == "true";
    }


    /**
     * @return bool
     */
    public function getValue()
    {
        return $this->value;
    }
}