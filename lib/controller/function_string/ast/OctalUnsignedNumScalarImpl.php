<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 10:29 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class OctalUnsignedNumScalarImpl implements UnsignedNumScalar{
    private $value;

    function __construct($value)
    {
        $this->value =  intval($value, 8);
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}