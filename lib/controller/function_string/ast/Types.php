<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 8:24 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


interface Types extends Type{

    /**
     * @return Name
     */
    public function getName();

    /**
     * @return Type
     */
    public function getType();
}