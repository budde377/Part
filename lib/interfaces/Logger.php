<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/3/12
 * Time: 9:31 PM
 * To change this template use File | Settings | File Templates.
 */
interface Logger
{
    /**
     * @abstract
     * Logs message in specified
     * @param Object $caller
     * @param $message
     */
    public function log($caller, $message);
}
