<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/15
 * Time: 10:43 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;



use ChristianBudde\cbweb\controller\json\Program as JProgram;

class CompositeFunctionProgramImpl implements Program{

    private $compositeFunction;
    private $type;

    function __construct(Type $type, CompositeFunction $compositeFunction)
    {
        $this->type = $type;
        $this->compositeFunction = $compositeFunction;
    }

    /**
     * @return mixed
     */
    public function getCompositeFunction()
    {
        return $this->compositeFunction;
    }



    /**
     * @return Type
     */
    public function getType()
    {

        return $this->type;
    }

    /**
     * @return JProgram
     */
    public function toJSONProgram()
    {
        return $this->compositeFunction->toJSONCompositeFunction($this->getType()->toJSONTarget());
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