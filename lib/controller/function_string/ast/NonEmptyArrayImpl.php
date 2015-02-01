<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:18 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NonEmptyArrayImpl implements NonEmptyArray{
    private $arrayEntry;

    function __construct(ArrayEntry $arrayEntry)
    {
        $this->arrayEntry = $arrayEntry;
    }


    /**
     * @return ArrayEntry
     */
    public function getArrayEntry()
    {
        return $this->arrayEntry;
    }
}