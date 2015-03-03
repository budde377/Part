<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 6:15 PM
 */

namespace ChristianBudde\Part\test;


use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubCurrentPageStrategyImpl;
use ChristianBudde\Part\test\stub\StubSiteImpl;
use ChristianBudde\Part\test\stub\StubUserImpl;
use ChristianBudde\Part\test\stub\StubUserLibraryImpl;
use ChristianBudde\Part\util\CacheControlImpl;
use ChristianBudde\Part\util\script\Script;
use ChristianBudde\Part\util\script\UserLoginCheckPreScriptImpl;
use ChristianBudde\Part\Website;

class UserLoginCheckPreScriptImplTest extends \PHPUnit_Framework_TestCase{
    /** @var  StubBackendSingletonContainerImpl */
    private $container;
    /** @var  CacheControlImpl */
    private $cacheControl;
    /** @var  StubUserLibraryImpl */
    private $userLibrary;
    /** @var  Script */
    private $script;

    protected function setUp()
    {
        $this->container = new StubBackendSingletonContainerImpl();
        $this->container->setUserLibraryInstance($this->userLibrary = new StubUserLibraryImpl());
        $this->container->setCacheControlInstance($this->cacheControl = new CacheControlImpl(new StubSiteImpl(), new StubCurrentPageStrategyImpl()));
        $this->script = new UserLoginCheckPreScriptImpl($this->container);
    }


    public function testRunWithNullUserDoesNothing()
    {

        $this->script->run(Website::WEBSITE_SCRIPT_TYPE_PRESCRIPT, null);
        $this->assertTrue($this->cacheControl->isEnabled());

    }

    public function testRunWithUserDoesDisable()
    {
        $this->userLibrary->setUserLoggedIn(new StubUserImpl());
        $this->script->run(Website::WEBSITE_SCRIPT_TYPE_PRESCRIPT, null);
        $this->assertFalse($this->cacheControl->isEnabled());
    }

    public function testRunAsPostScriptDoesNothing()
    {
        $this->userLibrary->setUserLoggedIn(new StubUserImpl());
        $this->script->run(Website::WEBSITE_SCRIPT_TYPE_POSTSCRIPT, null);
        $this->assertTrue($this->cacheControl->isEnabled());
    }


}