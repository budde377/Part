<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:29 PM
 */

namespace ChristianBudde\Part\controller\function_string\ast;




use ChristianBudde\Part\controller\json\Type as JType;
use ChristianBudde\Part\controller\json\TypeImpl;

class NameImpl implements Name{

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


    /**
     * @return JType
     */
    public function toJSONTarget()
    {
        return new TypeImpl($this->getValue());
    }
}