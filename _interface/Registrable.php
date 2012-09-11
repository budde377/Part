<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 6:11 PM
 * To change this template use File | Settings | File Templates.
 */
interface Registrable
{

    /**
     * @abstract
     * @param $id string
     * @return string | null Will return string if id is found, else null
     */
    public function callback($id);

}
