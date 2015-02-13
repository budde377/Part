<?php

namespace ChristianBudde\Part\test;

use ChristianBudde\Part\BackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubConfigImpl;
use ChristianBudde\Part\test\stub\StubScriptImpl;
use ChristianBudde\Part\test\stub\StubSiteFactoryImpl;
use ChristianBudde\Part\util\script\ScriptChainImpl;
use ChristianBudde\Part\Website;
use ChristianBudde\Part\WebsiteImpl;
use PHPUnit_Framework_TestCase;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/28/12
 * Time: 3:52 PM
 * To change this template use File | Settings | File Templates.
 */
class WebsiteImplTest extends PHPUnit_Framework_TestCase
{

    /** @var $factory \ChristianBudde\Part\test\stub\StubSiteFactoryImpl */
    public $factory;

    protected function setUp()
    {
        $this->factory = new StubSiteFactoryImpl();
    }


    public function testWebsiteWillRunPreScriptOnConstructWithStringPreScriptAsNameAndNullAsArg()
    {
        $chain = new ScriptChainImpl();
        $script = new StubScriptImpl();
        $config = new StubConfigImpl();
        $chain->addScript($script);
        $this->factory->setPreScriptChain($chain);
        $this->factory->setConfig($config);
        $this->factory->setBackendSingletonContainer(new BackendSingletonContainerImpl($config));

        new WebsiteImpl($this->factory);
        $this->assertEquals(1, $script->getNumRuns(), 'The PreScripts did not run the right number of times at construct');
        $this->assertEquals(Website::WEBSITE_SCRIPT_TYPE_PRESCRIPT, $script->getLastRunName(), 'The preScript did not run with preScript as name');
        $this->assertNull($script->getLastRunArgs(), 'The preScript did not run with arg null');
    }

    public function testWebsiteWillRunPostScriptOnDestructWithStringPostScriptAsNameAndNullAsArg()
    {
        $config = new StubConfigImpl();

        $chain = new ScriptChainImpl();
        $script = new StubScriptImpl();
        $chain->addScript($script);
        $this->factory->setPostScriptChain($chain);
        $this->factory->setConfig($config);
        $this->factory->setBackendSingletonContainer(new BackendSingletonContainerImpl($config));

        $website = new WebsiteImpl($this->factory);
        unset($website);
        $this->assertEquals(1, $script->getNumRuns(), 'The postScripts did not run the right number of times at construct');
        $this->assertEquals(Website::WEBSITE_SCRIPT_TYPE_POSTSCRIPT, $script->getLastRunName(), 'The postScripts did not run with postScript as name');
        $this->assertNull($script->getLastRunArgs(), 'The postScripts did not run with arg null');
    }


}
