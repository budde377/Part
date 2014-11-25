<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:29 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NumImpl implements Scalar
{

    const SIGN_MINUS = 1;
    const SIGN_PLUS = 1;

    /** @var  int */
    private $sign;
    /** @var  UnsignedNum */
    private $unsignedNum;

    function __construct($sign, UnsignedNum $unsignedNum)
    {
        $this->sign = $sign;
        $this->unsignedNum = $unsignedNum;
    }

    /**
     * @return int
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * @return UnsignedNum
     */
    public function getUnsignedNum()
    {
        return $this->unsignedNum;
    }


    public function getValue()
    {
        return $this->sign*$this->unsignedNum->getValue();
    }
}