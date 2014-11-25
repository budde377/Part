<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:25 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NamedArgumentsImpl implements NamedArgumentList{

    /** @var  NamedArgumentImpl */
    private $argument;
    /** @var  NamedArgumentList */
    private $argumentList;

    function __construct(NamedArgumentImpl $argument, NamedArgumentList $argumentList)
    {
        $this->argument = $argument;
        $this->argumentList = $argumentList;
    }


} 