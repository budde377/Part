<?php
namespace ChristianBudde\cbweb;use JsonSerializable;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 7:59 PM
 */

interface JSONObjectSerializable extends JsonSerializable{

    /**
     * Serializes the object to an instance of JSONObject.
     * @return JSONObject
     */

    public function jsonObjectSerialize();
} 