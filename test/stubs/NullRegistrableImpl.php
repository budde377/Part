<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 6:25 PM
 * To change this template use File | Settings | File Templates.
 */
class NullRegistrableImpl implements Registrable
{

    private $returnArray;

    /**
     * @param $returnArray array | null
     */
    public function __construct($returnArray = null)
    {
        $this->returnArray = is_array($returnArray) ? $returnArray : array();
    }

    /**
     * @param $id string
     * @return string | null
     */
    public function callback($id)
    {
        return isset($this->returnArray[$id]) ? $this->returnArray[$id] : null;
    }
}
