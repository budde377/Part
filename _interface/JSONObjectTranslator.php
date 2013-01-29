<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 10:00
 * To change this template use File | Settings | File Templates.
 */
interface JSONObjectTranslator
{

    /**
     * This will encode a object matching the current JSONObject into a JSONObject
     * @param $object
     * @return JSONObject | bool Will return JSONObject on success else FALSE
     */
    public function encode($object);

    /**
     * This will decode an json object to
     * @param JSONObject $jsonObject
     * @return mixed
     */
    public function decode($jsonObject);

}
