<?php

namespace ChristianBudde\Part;


use ChristianBudde\Part\util\task\TaskQueueImpl;
use ChristianBudde\Part\util\task\StubTaskImpl;
use PHPUnit_Framework_TestCase;

/**
 * User: budde
 * Date: 5/28/12
 * Time: 3:52 PM
 */
class WebsiteImplTest extends PHPUnit_Framework_TestCase
{

    /** @var $factory StubSiteFactoryImpl */
    public $factory;

    protected function setUp()
    {
        $this->factory = new StubSiteFactoryImpl();
    }


    public function testWebsiteWillRunPreScriptOnConstructWithStringPreScriptAsNameAndNullAsArg()
    {
        $chain = new TaskQueueImpl();
        $script = new StubTaskImpl();
        $config = new StubConfigImpl();
        $chain->addTask($script);
        $this->factory->setPreScriptChain($chain);
        $this->factory->setConfig($config);
        $this->factory->setBackendSingletonContainer(new BackendSingletonContainerImpl($config));

        new WebsiteImpl($this->factory);
        $this->assertEquals(1, $script->getNumRuns(), 'The PreScripts did not run the right number of times at construct');
    }

    public function testWebsiteWillRunPostScriptOnDestructWithStringPostScriptAsNameAndNullAsArg()
    {
        $config = new StubConfigImpl();

        $chain = new TaskQueueImpl();
        $script = new StubTaskImpl();
        $chain->addTask($script);
        $this->factory->setPostScriptChain($chain);
        $this->factory->setConfig($config);
        $this->factory->setBackendSingletonContainer(new BackendSingletonContainerImpl($config));

        $website = new WebsiteImpl($this->factory);
        unset($website);
        $this->assertEquals(1, $script->getNumRuns(), 'The postScripts did not run the right number of times at construct');
    }


}
