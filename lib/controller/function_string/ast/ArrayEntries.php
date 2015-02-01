<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:21 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


interface ArrayEntries extends ArrayEntry{

    /**
     * @return ScalarArrayProgram
     */
    public function getValue();

    /**
     * @return ArrayEntry
     */
    public function getArrayEntry();

}