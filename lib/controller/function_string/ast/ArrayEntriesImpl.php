<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:27 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\util\traits\ParserTrait;

class ArrayEntriesImpl implements ArrayEntries{

    use ParserTrait;

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
        return $this->merge_arrays($this->getValue()->toArray(), $this->getArrayEntry()->toArray());
    }
}