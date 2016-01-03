<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/8/13
 * Time: 10:25 PM
 * To change this template use File | Settings | File Templates.
 */

namespace ChristianBudde\Part\util;

use ChristianBudde\Part\model\page\StubCurrentPageStrategyImpl;
use ChristianBudde\Part\model\page\StubPageImpl;
use ChristianBudde\Part\model\site\StubSiteImpl;
use PHPUnit_Framework_TestCase;

class CacheControlImplTest extends PHPUnit_Framework_TestCase
{

    /** @var  CacheControlImpl */
    private $cacheControl;
    private $stubStrat;
    private $stubPage;
    private $stubSite;

    public function setUp()
    {
        $this->stubPage = new StubPageImpl();
        $this->stubStrat = new StubCurrentPageStrategyImpl();
        $this->stubStrat->setCurrentPagePath(array($this->stubPage));
        $this->stubStrat->setCurrentPage($this->stubPage);
        $this->stubSite = new StubSiteImpl();
        $this->cacheControl = new CacheControlImpl($this->stubSite, $this->stubStrat);
    }


    public function testCacheIsEnabledPrDefault()
    {
        $this->assertTrue($this->cacheControl->isEnabled());
    }

    public function testDisableCacheWillDisableCache()
    {
        $this->cacheControl->disableCache();
        $this->assertFalse($this->cacheControl->isEnabled());
    }

    /**
     * @runInSeparateProcess
     */
    public function testCantDisableAfterSetUp()
    {

        $this->assertFalse($this->cacheControl->setUpCache());
        $this->cacheControl->disableCache();
        $this->assertTrue($this->cacheControl->isEnabled());
    }


}