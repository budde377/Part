<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:27 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class ArrayEntriesImpl implements PositionalArrayEntry{

    /** @var  ArrayEntryImpl */
    private $entry;

    /** @var  AllArrayEntries */
    private $arrayEntries;

    function __construct(ArrayEntryImpl $entry, AllArrayEntries $arrayEntries)
    {
        $this->entry = $entry;
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
     * @return ArrayEntryImpl
     */
    public function getEntry()
    {
        return $this->entry;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->getEntry()->toArray(), $this->getArrayEntries()->toArray());
    }
}