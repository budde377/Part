<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:05 AM
 */

namespace ChristianBudde\Part\controller\function_string\ast;



class ArgumentsImpl implements Arguments{

    private $sap;
    private $argument;

    function __construct(ScalarArrayProgram $sap, Argument $argument)
    {
        $this->sap = $sap;
        $this->argument = $argument;
    }


    /**
     * @return ScalarArrayProgram
     */
    public function getValue()
    {
        return $this->sap;
    }

    /**
     * @return Argument
     */
    public function getArgument()
    {
        return $this->argument;
    }

    /**
     * @return array
     */
    public function toArgumentArray()
    {
        return array_merge($this->getValue()->toArgumentArray(), $this->getArgument()->toArgumentArray());
    }


}