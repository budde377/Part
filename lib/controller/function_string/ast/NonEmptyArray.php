<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:19 AM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


interface NonEmptyArray extends AArray{

    /**
     * @return ArrayEntry
     */
    public function getArrayEntry();

}