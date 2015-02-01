<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:23 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;



use ChristianBudde\cbweb\controller\json\Type as JType;
use ChristianBudde\cbweb\controller\json\TypeImpl;

class TypesImpl implements Types{

    /** @var  Type */
    private $type;
    /** @var  Name */
    private $name;

    function __construct(Name $name, Type $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Type
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
        return new TypeImpl($this->name->getValue()."\\".$this->getType()->toJSONTarget()->getTypeString());
    }
}