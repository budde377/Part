<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:26 PM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class ArrayImpl implements ScalarArrayProgram{

    /** @var  AllArrayEntries */
    private $arrayEntries;

    function __construct(AllArrayEntries $arrayEntries)
    {
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
     * @return ArgumentList[]
     */
    public function toArgumentList()
    {
        return [$this];
    }

    /**
     * @return ScalarArrayProgram[]
     */
    public function toArray()
    {
        return $this->arrayEntries->toArray();
    }

    /**
     * @param callable $programComputer
     * @return mixed
     */
    public function compute(callable $programComputer)
    {
        $r = [];
        foreach($this->toArray() as $k=>$v){
            $r[$k] = $v->compute($programComputer);
        }

        return $r;
    }
}