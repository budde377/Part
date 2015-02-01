<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 10:20 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NullScalarImpl implements Scalar{

    /**
     * @return mixed
     */
    public function getValue()
    {
        return null;
    }
}