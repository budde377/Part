<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/28/14
 * Time: 12:55 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class ArgumentImpl implements ArgumentList{

    /** @var  ScalarArrayProgram */
    private $value;

    function __construct(ScalarArrayProgram $value)
    {
        $this->value = $value;
    }

    /**
     * @return ScalarArrayProgram
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @return ArgumentList[]
     */
    public function toArgumentList()
    {
        return [$this];
    }
}