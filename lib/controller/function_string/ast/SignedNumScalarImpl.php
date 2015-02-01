<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 10:24 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class SignedNumScalarImpl implements SignedNumScalar{

    private $sign;
    private $number;

    function __construct($sign, UnsignedNumScalar $number)
    {
        $this->sign = $sign;
        $this->number = $number;
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return ($this->sign == "+"?1:-1)*$this->getNumber()->getValue();
    }

    /**
     * @return string
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * @return UnsignedNumScalar
     */
    public function getNumber()
    {
        return $this->number;
    }
}