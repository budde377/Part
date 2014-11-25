<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:28 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class NamedArrayEntriesImpl implements NamedArrayEntry{

    /** @var  KeyArrowValueImpl */
    private $value;
    /** @var  AllArrayEntries */
    private $arrayEntries;

    function __construct(KeyArrowValueImpl $value, AllArrayEntries $arrayEntries)
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
     * @return KeyArrowValueImpl
     */
    public function getValue()
    {
        return $this->value;
    }



} 