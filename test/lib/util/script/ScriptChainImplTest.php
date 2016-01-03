<?php
namespace ChristianBudde\Part\util\script;

use PHPUnit_Framework_TestCase;

/**
 * User: budde
 * Date: 5/16/12
 * Time: 9:02 AM
 */
class ScriptChainImplTest extends PHPUnit_Framework_TestCase
{

    public function testScriptChainWillRunAddedScriptOnce()
    {
        $script = new StubScriptImpl();
        $chain = new ScriptChainImpl();
        $before = $script->getNumRuns();
        $chain->addScript($script);
        $chain->run('', null);
        $after = $script->getNumRuns();
        $this->assertEquals(1, $after - $before, 'The Script did not run once');

    }

    public function testScriptChainWillRunScriptsInAddedOrder()
    {
        $script1 = new StubScriptImpl();
        $script2 = new StubScriptImpl();
        $chain = new ScriptChainImpl();
        $chain->addScript($script1);
        $chain->addScript($script2);
        $chain->run('test', null);
        $this->assertGreaterThan($script1->lastRunAt(), $script2->lastRunAt(), "The Scripts did not run in added order.");
    }


    public function testScriptChainParseNameAndArgumentsToScript()
    {
        $script = new StubScriptImpl();
        $chain = new ScriptChainImpl();
        $chain->addScript($script);
        $chain->run('test', array('test' => 'test'));
        $this->assertEquals('test', $script->getLastRunName(), 'The right name was not parsed to script');


        $argArray = $script->getLastRunArgs();
        $this->assertArrayHasKey('test', $argArray, 'The argument did not contain the right index');
        $this->assertEquals('test', $argArray['test'], 'The argument did not have the right value');
    }

}
