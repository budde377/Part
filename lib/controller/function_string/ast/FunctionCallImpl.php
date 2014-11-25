<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:14 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class FunctionCallImpl implements Program, Target{

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




}