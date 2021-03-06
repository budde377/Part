<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:27 AM
 */

namespace ChristianBudde\Part\controller\function_string\ast;



class ArrayEntriesImpl implements ArrayEntries{


    private $scalarArrayProgram;
    private $arrayEntry;

    function __construct(ScalarArrayProgram $scalarArrayProgram, ArrayEntry $arrayEntry)
    {
        $this->scalarArrayProgram = $scalarArrayProgram;
        $this->arrayEntry = $arrayEntry;
    }


    /**
     * @return ScalarArrayProgram
     */
    public function getValue()
    {
        return $this->scalarArrayProgram;
    }

    /**
     * @return ArrayEntry
     */
    public function getArrayEntry()
    {
        return $this->arrayEntry;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->getValue()->toArray(), $this->getArrayEntry()->toArray());
    }
}