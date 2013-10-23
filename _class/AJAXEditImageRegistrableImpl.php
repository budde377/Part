<?php
require_once dirname(__FILE__) . "/JSONFunctionImpl.php";
require_once dirname(__FILE__) . '/../_interface/Registrable.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/6/13
 * Time: 2:32 PM
 * To change this template use File | Settings | File Templates.
 */

class AJAXEditImageRegistrableImpl implements Registrable{

    /**
     * @param $id string
     * @return string | null Will return string if id is found, else null
     */
    public function callback($id)
    {
        // TODO: Implement callback() method.
    }
}