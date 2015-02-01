<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:22 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


interface NamedArrayEntry extends ArrayEntry{

    /**
     * @return Scalar
     */
    public function getName();

    /**
     * @return ScalarArrayProgram
     */
    public function getValue();

}