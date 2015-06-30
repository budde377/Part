<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 6/30/15
 * Time: 7:45 PM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


abstract class ProgramImpl implements Program{

    /** @var  Type */
    protected $type;

    function __construct(Type $type)
    {
        $this->type = $type;
    }


    /**
     * @return Type
     */
    public function getType()
    {

        return $this->type;
    }


    public function toJSON()
    {
        return $this->toJSONProgram();
    }

    /**
     * @return array
     */
    public function toArgumentArray()
    {
        return [$this->toJSON()];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [$this->toJSON()];
    }
}