<?php
namespace ChristianBudde\cbweb\util\script;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:51 AM
 * To change this template use File | Settings | File Templates.
 */
interface ScriptChain
{
    /**
     * @abstract
     * Runs all scripts in chain in the order submitted.
     * @param $name string
     * @param $args array | null
     */
    public function run($name, $args);

    /**
     * @abstract
     * Adds a script to the chain
     * @param Script $script
     */
    public function addScript(Script $script);


}
