<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/15
 * Time: 10:43 PM
 */

namespace ChristianBudde\Part\controller\function_string\ast;



use ChristianBudde\Part\controller\json\Program as JProgram;

class CompositeFunctionProgramImpl extends ProgramImpl{

    private $compositeFunction;

    function __construct(Type $type, CompositeFunction $compositeFunction)
    {
        parent::__construct($type);
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
     * @return JProgram
     */
    public function toJSONProgram()
    {
        return $this->compositeFunction->toJSONCompositeFunction($this->getType()->toJSONTarget());
    }

}