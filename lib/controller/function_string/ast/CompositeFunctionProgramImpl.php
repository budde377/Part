<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/15
 * Time: 10:43 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;



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
}