<?php
namespace ChristianBudde\Part\util\script;
/**
 * User: budde
 * Date: 5/10/12
 * Time: 10:51 AM
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
