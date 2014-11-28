<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:14 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\controller\json\JSONFunction;
use ChristianBudde\cbweb\controller\json\JSONFunctionImpl;
use ChristianBudde\cbweb\controller\json\Type as JType;

class FunctionCallImpl extends Program implements  Target{

    /** @var  Target */
    private $target;
    /** @var  FFunction */
    private $function;

    function __construct(Target $target, FFunction $function)
    {
        $this->target = $target;
        $this->function = $function;
    }

    /**
     * @return FFunction
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @return Target
     */
    public function getTarget()
    {
        return $this->target;
    }


    /**
     * @return JSONFunction
     */
    public function toJSONProgram()
    {
        if($this->function instanceof ArrayAccessFunctionImpl){
            $name = "arrayAccess";
        } else if($this->function instanceof ArgumentNamedFunction){
            $name = $this->function->getName()->getValue();
        } else {
            return null;
        }

        $f = new JSONFunctionImpl($name, $this->target->toJSONTarget(), array_map(function(ArgumentList $argumentList){
            if($argumentList instanceof NamedArgumentImpl || $argumentList instanceof ArgumentImpl){
                $argumentList = $argumentList->getValue();
            }

            if($argumentList instanceof ScalarArrayProgram){
                return $argumentList->compute(function (Program $program){
                    return $program->toJSONProgram();
                });
            }
            return null;

        }, $this->function->generateArgumentList()));



        return $f;
    }

    /**
     * @return JType
     */
    public function toJSONTarget()
    {
        return $this->toJSONProgram();
    }


}