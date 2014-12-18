<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:32 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class ExpDoubleNumberImpl extends Float{

    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }
} 