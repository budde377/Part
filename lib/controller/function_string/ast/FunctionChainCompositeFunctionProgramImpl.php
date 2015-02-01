<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/15
 * Time: 10:44 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;



use ChristianBudde\cbweb\controller\json\Program as JProgram;

class FunctionChainCompositeFunctionProgramImpl implements Program{

    private $functionChain;
    private $compositeFunction;
    private $type;

    function __construct(Type $type, FunctionChain $functionChain, CompositeFunction $compositeFunction)
    {
        $this->type = $type;
        $this->functionChain = $functionChain;
        $this->compositeFunction = $compositeFunction;
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
        return $this->compositeFunction->toJSONCompositeFunction($this->functionChain->toJSONFunction($this->type->toJSONTarget()));
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