<?php
namespace ChristianBudde\Part\util\script;
use ChristianBudde\Part\exception\ScriptHasRunException;

/**
 * User: budde
 * Date: 5/28/12
 * Time: 3:03 PM
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
