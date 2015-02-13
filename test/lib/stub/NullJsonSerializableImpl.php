<?php
namespace ChristianBudde\Part\test\stub;
use JsonSerializable;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 7:40 PM
 */

class NullJsonSerializableImpl implements JsonSerializable
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
        return [];
    }
}