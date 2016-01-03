<?php
namespace ChristianBudde\Part\controller\json;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 8:13 PM
 */

class NullObjectSerializableImpl implements JSONObjectSerializable
{


    /**
     * Serializes the object to an instance of JSONObject.
     * @return \ChristianBudde\Part\controller\json\Object
     */
    public function jsonObjectSerialize()
    {
        return new ObjectImpl(null);
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
        return $this->jsonObjectSerialize()->jsonSerialize();
    }
}