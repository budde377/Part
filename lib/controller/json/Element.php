<?php
namespace ChristianBudde\Part\controller\json;

use JsonSerializable;

/**
 * User: budde
 * Date: 22/01/13
 * Time: 10:00
 */
interface Element extends JsonSerializable
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
