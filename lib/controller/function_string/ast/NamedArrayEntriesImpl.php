<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:54 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


use ChristianBudde\cbweb\util\traits\ParserTrait;

class NamedArrayEntriesImpl implements NamedArrayEntries{

    use ParserTrait;

    private $name;
    private $value;
    private $arrayEntry;

    function __construct(Scalar $name, ScalarArrayProgram $value, ArrayEntry $arrayEntry)
    {
        $this->name = $name;
        $this->value = $value;
        $this->arrayEntry = $arrayEntry;
    }


    /**
     * @return Scalar
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ScalarArrayProgram
     */
    public function getValue()
    {
        return $this->value;
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
        return $this->merge_arrays([$this->name->getValue() => $this->getValue()->toJSON()] , $this->getArrayEntry()->toArray());
    }
}