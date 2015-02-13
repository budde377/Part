<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 10:16 AM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


use ChristianBudde\Part\util\traits\ParserTrait;

class SingleQuotedStringScalarImpl implements StringScalar{

    use ParserTrait;

    private $value;

    function __construct($value)
    {
        $this->value = $this->singleQuotedStringToString($value);
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function toJSON()
    {
        return $this->getValue();
    }


    /**
     * @return array
     */
    public function toArgumentArray()
    {
        return [$this->getValue()];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->toArgumentArray();
    }
}