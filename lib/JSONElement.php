<?php
namespace ChristianBudde\cbweb;use JsonSerializable;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 10:00
 * To change this template use File | Settings | File Templates.
 */
interface JSONElement extends JsonSerializable
{

    /**
     * @return string
     */
    public function getAsJSONString();

    /**
     * @return array
     */
    public function getAsArray();
}
