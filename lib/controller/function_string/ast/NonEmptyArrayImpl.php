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

    /**
     * @return array
     */
    public function toArgumentArray()
    {
        return [$this->toJSON()];
    }

    public function toJSON()
    {
        $array = $this->getArrayEntry()->toArray();
        $retArray = [];

        foreach($array as $entry){
            if($entry instanceof NamedArrayEntry){
                $retArray[$entry->getName()->getValue()] = $entry->getValue()->toJSON();
            } else {
                $retArray[] = $entry;
            }

        }

        return $retArray;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->toArgumentArray();
    }
}