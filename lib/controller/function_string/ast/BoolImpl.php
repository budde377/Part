<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 11:08 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class BoolImpl implements Scalar{

    /** @var  bool */
    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return boolean
     */
    public function getValue()
    {
        return $this->value;
    }




} 