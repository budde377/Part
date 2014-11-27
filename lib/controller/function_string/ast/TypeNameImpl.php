<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:23 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\controller\json\Type as JType;

class TypeNameImpl implements Type{

    /** @var  NameImpl */
    private $type;
    /** @var  Type */
    private $name;

    function __construct(Type $type, NameImpl $name)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return Type
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return NameImpl
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @return JType
     */
    public function toJSONTarget()
    {
        return "{$this->type->toJSONTarget()->getTypeString()}\\{$this->getName()}";
    }
}