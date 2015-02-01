<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 10:28 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class DecimalUnsignedNumScalarImpl implements UnsignedNumScalar{

    private $value;

    function __construct($value)
    {
        $this->value =  intval($value);
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function toJSON()
    {
        return $this->getValue();
    }

    /**
     * @return array
     */
    public function toArgumentArray()
    {
        return [$this->getValue()];
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return $this->toArgumentArray();
    }
}