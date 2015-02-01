<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 10:12 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\util\traits\ParseTrait;

class DoubleQuotedStringScalarImpl implements Scalar{

    use ParseTrait;

    private $value;

    function __construct($value)
    {
        $this->value = $this->doubleQuotedStringToString($value);
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}