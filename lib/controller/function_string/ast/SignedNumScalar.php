<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 10:22 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


interface SignedNumScalar extends NumScalar{


    /**
     * @return string
     */
    public function getSign();

    /**
     * @return UnsignedNumScalar
     */
    public function getNumber();

}