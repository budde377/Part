<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 10:16 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\util\traits\ParseTrait;

class SingleQuotedStringScalarImpl implements StringScalar{

    use ParseTrait;

    private $value;

    function __construct($value)
    {
        $this->value = $this->singleQuotedStringToString($value);
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}