<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:21 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class ArrayAccessFunctionImpl implements FFunction{

    /** @var  ScalarArrayProgram */
    private $key;

    function __construct(ScalarArrayProgram $key)
    {
        $this->key = $key;
    }

    /**
     * @return ScalarArrayProgram
     */
    public function getKey()
    {
        return $this->key;
    }




} 