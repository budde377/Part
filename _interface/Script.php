<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:51 AM
 * To change this template use File | Settings | File Templates.
 */
interface Script
{

    /**
     * This function runs the script
     * @abstract
     * @param $name string
     * @param $args array | null
     */
    public function run($name, $args);
}
