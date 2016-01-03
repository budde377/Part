<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 6:15 PM
 */

namespace ChristianBudde\Part\util\script;


use ChristianBudde\Part\model\page\StubCurrentPageStrategyImpl;
use ChristianBudde\Part\model\site\StubSiteImpl;
use ChristianBudde\Part\model\user\StubUserImpl;
use ChristianBudde\Part\model\user\StubUserLibraryImpl;
use ChristianBudde\Part\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\util\CacheControlImpl;
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