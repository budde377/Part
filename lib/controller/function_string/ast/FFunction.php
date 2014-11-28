<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:19 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


abstract class FFunction implements FunctionChain{

    /**
     * @return ArgumentList[]
     */
    abstract  public function generateArgumentList();

    /**
     * @return ArgumentImpl[]
     */
    abstract  public function generatePositionalArgumentList();

    /**
     * @return NamedArgumentImpl[]
     */
    abstract public function generateNamedArgumentList();

    /**
     * @param Target $target
     * @return FunctionCallImpl
     */
    public function toFunctionCall(Target $target)
    {
        return new FunctionCallImpl($target, $this);
    }


} 