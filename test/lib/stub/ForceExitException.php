<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/6/15
 * Time: 6:42 PM
 */

namespace ChristianBudde\Part\test\stub;


class ForceExitException extends  \Exception{

    public $data;

    function __construct($data = null)
    {
        $this->data = $data;
    }


}