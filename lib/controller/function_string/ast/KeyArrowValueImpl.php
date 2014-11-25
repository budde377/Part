<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:28 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class KeyArrowValueImpl implements NamedArrayEntry{

    /** @var  Scalar */
    private $key;

    /** @var  ScalarArrayProgram */
    private $value;

    function __construct(Scalar $key, ScalarArrayProgram $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return Scalar
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return ScalarArrayProgram
     */
    public function getValue()
    {
        return $this->value;
    }






} 