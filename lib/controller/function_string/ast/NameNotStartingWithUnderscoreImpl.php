<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:00 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NameNotStartingWithUnderscoreImpl implements NameNotStartingWithUnderscore{
    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

}