<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/15
 * Time: 10:44 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;



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
}