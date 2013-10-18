<?php
require_once dirname(__FILE__).'/../_interface/Config.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/15/12
 * Time: 8:17 AM
 */
interface AJAXRegister
{

    /**
     * @abstract
     * Register AJAX, with unique id, duplicates will not be added.
     * @param $id string
     * @param Registrable $callback
     */
    public function registerAJAX($id, Registrable $callback);

    /**
     * @param Config $config
     * @return void
     */
    public function registerAJAXFromConfig(Config $config);
    /**
     * @abstract
     * Returns the result of callback() function on the registrable object.
     * @param $id string
     * @return string | null Will return null if id is not found else string
     */
    public function getAJAXFromRegistered($id);


    /**
     * Returns the result of callback() function on the registrable object.
     * ID calculated from function name such as Page.[fn](..)
     * @param string $functionName
     * @return string | null Will return null if id is not found else string
     */
    public function getAJAXFromRegisteredFromFunctionName($functionName);


    /**
     * @abstract
     * List all registered AJAX as an array with id's as entry
     * @return array
     */
    public function listRegistered();

}
