<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/15
 * Time: 10:44 PM
 */

namespace ChristianBudde\Part\controller\function_string\ast;



use ChristianBudde\Part\controller\json\Program as JProgram;

class FunctionChainProgramImpl implements Program{

    private $functionChain;
    private $type;

    function __construct(Type $type, FunctionChain $functionChain)
    {
        $this->type = $type;
        $this->functionChain = $functionChain;
    }



    /**
     * @return mixed
     */
    public function getFunctionChain()
    {
        return $this->functionChain;
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
        return $this->functionChain->toJSONFunction($this->type->toJSONTarget());
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