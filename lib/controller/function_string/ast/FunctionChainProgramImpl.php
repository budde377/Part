<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/15
 * Time: 10:44 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;



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
}