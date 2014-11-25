<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:27 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class ArrayEntriesImpl implements ArrayEntry{

    /** @var  ScalarArrayProgram */
    private $value;

    /** @var  AllArrayEntries */
    private $arrayEntries;

    function __construct(ScalarArrayProgram $value, AllArrayEntries$arrayEntries)
    {
        $this->value = $value;
        $this->arrayEntries = $arrayEntries;
    }

    /**
     * @return AllArrayEntries
     */
    public function getArrayEntries()
    {
        return $this->arrayEntries;
    }

    /**
     * @return ScalarArrayProgram
     */
    public function getValue()
    {
        return $this->value;
    }




} 