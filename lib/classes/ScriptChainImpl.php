<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/11/12
 * Time: 12:46 PM
 * To change this template use File | Settings | File Templates.
 */
class ScriptChainImpl implements ScriptChain
{

    private $scriptChain = array();

    /**
     * Runs all scripts in chain in the order submitted.
     * @param string $name
     * @param array | null $args
     */
    public function run($name, $args)
    {
        foreach ($this->scriptChain as $script) {
            /** @var $script Script */
            $script->run($name, $args);
        }
    }

    /**
     * Adds a script to the chain
     * @param Script $script
     */
    public function addScript(Script $script)
    {
        $this->scriptChain[] = $script;
    }
}
