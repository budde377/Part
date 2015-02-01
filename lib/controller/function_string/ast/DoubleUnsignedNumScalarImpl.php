<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 10:27 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class DoubleUnsignedNumScalarImpl implements UnsignedNumScalar{

    private $value;

    function __construct($value)
    {
        $this->value =  floatval($value);
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}