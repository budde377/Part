<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:45 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NoArgumentNamedFunctionImpl implements  NoArgumentNamedFunction{

    private  $name;

    function __construct(Name $name)
    {
        $this->name = $name;
    }


    /**
     * @return Name
     */
    public function getName()
    {
        return $this->name;
    }
}