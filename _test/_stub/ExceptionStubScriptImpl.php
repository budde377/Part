<?php
require_once dirname(__FILE__) . '/../../_interface/Script.php';
require_once dirname(__FILE__) . '/ScriptHasRunException.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/28/12
 * Time: 3:03 PM
 * To change this template use File | Settings | File Templates.
 */
class ExceptionStubScriptImpl implements Script
{


    /**
     * This function runs the script
     * @param $name string
     * @param $args array | null
     * @throws ScriptHasRunException
     */
    public function run($name, $args)
    {
        throw new ScriptHasRunException($name, $args);
    }
}
