<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 11:09 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NullImpl implements Scalar{

    public function getValue()
    {
        return null;
    }
}