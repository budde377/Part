<?php
require_once dirname(__FILE__).'/_stub/StubCurrentPageStrategyImpl.php';
require_once dirname(__FILE__).'/_stub/StubPageImpl.php';
require_once dirname(__FILE__).'/../_class/CacheControlImpl.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/8/13
 * Time: 10:25 PM
 * To change this template use File | Settings | File Templates.
 */

class CacheControlImplTest extends  PHPUnit_Framework_TestCase{

    /** @var  CacheControlImpl */
    private $cacheControl;
    private $stubStrat;
    private $stubPage;

    public function setUp(){
        $this->stubPage = new StubPageImpl();
        $this->stubStrat = new StubCurrentPageStrategyImpl();
        $this->stubStrat->setCurrentPagePath(array($this->stubPage));
        $this->stubStrat->setCurrentPage($this->stubPage);
        $this->cacheControl = new CacheControlImpl($this->stubStrat);
    }


    public function testCacheIsEnabledPrDefault(){
        $this->assertTrue($this->cacheControl->isEnabled());
    }

    public function testDisableCacheWillDisableCache(){
        $this->cacheControl->disableCache();
        $this->assertFalse($this->cacheControl->isEnabled());
    }
    /**
     * @runInSeparateProcess
     */
    public function testCantDisableAfterSetUp(){

        $this->assertFalse($this->cacheControl->setUpCache());
        $this->cacheControl->disableCache();
        $this->assertTrue($this->cacheControl->isEnabled());
    }



}