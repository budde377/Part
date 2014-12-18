<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:26 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NamedArgumentImpl implements NamedArgumentList{
    /** @var  NameNotStartingWithUnderscoreImpl */
    private $name;
    /** @var  ScalarArrayProgram */
    private $value;

    function __construct(NameNotStartingWithUnderscoreImpl $name, ScalarArrayProgram $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return NameNotStartingWithUnderscoreImpl
     */
    public function getName()
    {
        return $this->name;
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