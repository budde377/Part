<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 8:13 PM
 */

class NullJSONObjectSerializableImpl implements JSONObjectSerializable{


    /**
     * Serializes the object to an instance of JSONObject.
     * @return JSONObject
     */
    public function jsonObjectSerialize()
    {
        return new JSONObjectImpl(null);
    }
}