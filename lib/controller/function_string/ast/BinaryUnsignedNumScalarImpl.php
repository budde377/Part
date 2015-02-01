<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 10:28 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class BinaryUnsignedNumScalarImpl {
    private $value;

    function __construct($value)
    {
        $this->value =  intval(substr($value, 2), 2);
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}