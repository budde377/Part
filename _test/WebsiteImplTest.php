<?php
require_once dirname(__FILE__) . '/_stub/StubSiteFactoryImpl.php';
require_once dirname(__FILE__) . '/_stub/StubScriptImpl.php';
require_once dirname(__FILE__) . '/_stub/StubConfigImpl.php';
require_once dirname(__FILE__) . '/../_class/WebsiteImpl.php';
require_once dirname(__FILE__) . '/../_class/ScriptChainImpl.php';


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/28/12
 * Time: 3:52 PM
 * To change this template use File | Settings | File Templates.
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
