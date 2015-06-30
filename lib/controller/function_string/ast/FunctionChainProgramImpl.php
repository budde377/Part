<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/15
 * Time: 10:44 PM
 */

namespace ChristianBudde\Part\controller\function_string\ast;



use ChristianBudde\Part\controller\json\Program as JProgram;

class FunctionChainProgramImpl extends ProgramImpl{

    private $functionChain;

    function __construct(Type $type, FunctionChain $functionChain)
    {
        parent::__construct($type);
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
     * @return JProgram
     */
    public function toJSONProgram()
    {
        return $this->functionChain->toJSONFunction($this->type->toJSONTarget());
    }

}