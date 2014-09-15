<?php
namespace ChristianBudde\cbweb\test\stub;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 8:13 PM
 */

class NullJSONObjectSerializableImpl implements \ChristianBudde\cbweb\controller\json\JSONObjectSerializable
{


    /**
     * Serializes the object to an instance of JSONObject.
     * @return \ChristianBudde\cbweb\controller\json\JSONObject
     */
    public function jsonObjectSerialize()
    {
        return new \ChristianBudde\cbweb\controller\json\JSONObjectImpl(null);
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