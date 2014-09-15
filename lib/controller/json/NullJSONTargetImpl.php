<?php
namespace ChristianBudde\cbweb\controller\json;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/31/14
 * Time: 4:27 PM
 */

class NullJSONTargetImpl implements JSONTarget{

    /**
     * @return string
     */
    public function getAsJSONString()
    {

    }

    /**
     * @return array
     */
    public function getAsArray()
    {

    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {

    }
}