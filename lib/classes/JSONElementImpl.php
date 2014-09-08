<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 2:31 PM
 */
abstract class JSONElementImpl implements JSONElement
{
    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->getAsArray();
    }

    /**
     * Will check input if it is scalars or elements, or arrays of these
     * @param mixed $val
     * @return bool
     */
    protected function validValue(&$val)
    {
        if (!is_array($val)) {
            return is_scalar($val) || $val instanceof JsonSerializable;
        }

        foreach ($val as $k => $v) {
            if (!$this->validValue($v)) {
                $val[$k] = null;
            } else {
                $val[$k] = $v;
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function getAsJSONString()
    {
        return json_encode($this);
    }

} 