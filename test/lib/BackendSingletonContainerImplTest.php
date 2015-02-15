<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 4:59 PM
 * To change this template use File | Settings | File Templates.
 */

namespace ChristianBudde\Part\test;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\BackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubConfigImpl;
use ChristianBudde\Part\test\util\CustomDatabaseTestCase;

class BackendSingletonContainerImplTest extends CustomDatabaseTestCase
{

    /** @var $config StubConfigImpl */
    private $config;
    /** @var $backContainer BackendSingletonContainerImpl */
    private $backContainer;
    /** @var  array */
    private $connectionArray;

    protected function setUp()
    {
        $this->connectionArray = array(
            'host' => self::$mysqlOptions->getHost(),
            'user' => self::$mysqlOptions->getUsername(),
            'password' => self::$mysqlOptions->getPassword(),
            'database' => self::$mysqlOptions->getDatabase());
        $this->config = new StubConfigImpl();
        $this->config->setMysqlConnection($this->connectionArray);
        $this->backContainer = new BackendSingletonContainerImpl($this->config);
    }

    public function testGetDBInstanceReturnsSameInstanceOfDB()
    {
        $db1 = $this->backContainer->getDBInstance();
        $this->assertInstanceOf('ChristianBudde\Part\util\db\DB', $db1, 'Did not return instance of DB');
        $this->config->setMysqlConnection(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $db2 = $this->backContainer->getDBInstance();
        $this->assertTrue($db1 == $db2, 'Did not reuse instance');
    }

    public function testGetCSSRegisterReturnsSameInstanceOfCSSRegister()
    {
        $css1 = $this->backContainer->getCSSRegisterInstance();
        $this->assertInstanceOf('ChristianBudde\Part\util\file\CSSRegister', $css1, 'Did not return instance of CSSRegister');
        $this->config->setMysqlConnection(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $css2 = $this->backContainer->getCSSRegisterInstance();
        $this->assertTrue($css1 === $css2, 'Did not reuse instance');

    }

    public function testGetDartRegisterReturnsSameInstanceOfDartRegister()
    {
        $dart1 = $this->backContainer->getDartRegisterInstance();
        $this->assertInstanceOf('ChristianBudde\Part\util\file\DartRegister', $dart1, 'Did not return instance of CSSRegister');
        $this->config->setMysqlConnection(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $dart2 = $this->backContainer->getDartRegisterInstance();
        $this->assertTrue($dart1 === $dart2, 'Did not reuse instance');

    }

    public function testGetJSRegisterReturnsSameInstanceOfJSRegister()
    {
        $js1 = $this->backContainer->getJSRegisterInstance();
        $this->assertInstanceOf('ChristianBudde\Part\util\file\JSRegister', $js1, 'Did not return instance of JSRegister');
        $this->config->setMysqlConnection(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $js2 = $this->backContainer->getJSRegisterInstance();
        $this->assertTrue($js1 === $js2, 'Did not reuse instance');

    }

    public function testGetAJAXServerReturnsSameInstanceOfAJAXServer()
    {
        $ajax1 = $this->backContainer->getAJAXServerInstance();
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\Server', $ajax1, 'Did not return instance of AJAXServer');
        $this->config->setMysqlConnection(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $ajax2 = $this->backContainer->getAJAXServerInstance();
        $this->assertTrue($ajax1 === $ajax2, 'Did not reuse instance');

    }

    public function testGetPageOrderRegisterReturnsSameInstanceOfPageOrder()
    {
        $this->config->setMysqlConnection($this->connectionArray);
        $pageOrder1 = $this->backContainer->getPageOrderInstance();
        $this->assertInstanceOf('ChristianBudde\Part\model\page\PageOrder', $pageOrder1, 'Did not return instance of PageOrder');
        $pageOrder2 = $this->backContainer->getPageOrderInstance();
        $this->assertTrue($pageOrder1 === $pageOrder2, 'Did not reuse instance');

    }

    public function testGetCurrentPageStrategyReturnsSameInstanceOfCurrentPageStrategy()
    {
        $this->config->setMysqlConnection($this->connectionArray);
        $pageOrder1 = $this->backContainer->getCurrentPageStrategyInstance();
        $this->assertInstanceOf('ChristianBudde\Part\model\page\CurrentPageStrategy', $pageOrder1, 'Did not return instance of CurrentPageStrategy');
        $pageOrder2 = $this->backContainer->getCurrentPageStrategyInstance();
        $this->assertTrue($pageOrder1 === $pageOrder2, 'Did not reuse instance');


    }

    public function testGetUserLibraryInstanceReturnsSameInstanceOfUserLibrary()
    {
        $this->config->setMysqlConnection($this->connectionArray);
        $ret1 = $this->backContainer->getUserLibraryInstance();
        $this->assertInstanceOf('ChristianBudde\Part\model\user\UserLibrary', $ret1, 'Did not return instance of SiteLibrary');
        $ret2 = $this->backContainer->getUserLibraryInstance();
        $this->assertTrue($ret1 === $ret2, 'Did not reuse instance');


    }


    public function testGetConfigInstanceWillReturnSameInstanceOfConfig()
    {
        $ret1 = $this->backContainer->getConfigInstance();
        $this->assertInstanceOf('ChristianBudde\Part\Config', $ret1, 'Did not return instance of Config');
        //$this->config->setMysqlCon(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $ret2 = $this->backContainer->getConfigInstance();
        $this->assertTrue($ret1 === $ret2, 'Did not reuse instance');

    }

    public function testGetDefaultPageLibraryWillReturnASameInstanceOfDefaultPageLibrary()
    {
        $ret1 = $this->backContainer->getDefaultPageLibraryInstance();
        $this->assertInstanceOf('ChristianBudde\Part\model\page\DefaultPageLibrary', $ret1, 'Did not return instance of DefaultPageLibrary');
        //$this->config->setMysqlCon(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $ret2 = $this->backContainer->getDefaultPageLibraryInstance();
        $this->assertTrue($ret1 === $ret2, 'Did not reuse instance');

    }

    public function testGetCacheControlWillReturnSameInstanceOfCacheControl()
    {
        $ret1 = $this->backContainer->getCacheControlInstance();
        $this->assertInstanceOf('ChristianBudde\Part\util\CacheControl', $ret1);
        $ret2 = $this->backContainer->getCacheControlInstance();
        $this->assertTrue($ret1 === $ret2);
    }

    public function testGetUpdaterWillReturnSameInstanceOfUpdater()
    {
        $ret1 = $this->backContainer->getUpdaterInstance();
        $this->assertInstanceOf('ChristianBudde\Part\model\updater\Updater', $ret1);
        $ret2 = $this->backContainer->getUpdaterInstance();
        $this->assertTrue($ret1 === $ret2);
    }

    public function testGetSiteVariablesWillReturnSameInstanceOfUpdater()
    {
        $ret1 = $this->backContainer->getSiteInstance();
        $this->assertInstanceOf('ChristianBudde\Part\model\site\Site', $ret1);
        $ret2 = $this->backContainer->getSiteInstance();
        $this->assertTrue($ret1 === $ret2);
    }

    public function testGetFileLibraryWillReturnSameInstance()
    {
        $ret1 = $this->backContainer->getFileLibraryInstance();
        $this->assertInstanceOf('ChristianBudde\Part\util\file\FileLibrary', $ret1);
        $ret2 = $this->backContainer->getFileLibraryInstance();
        $this->assertTrue($ret1 === $ret2);
    }

    public function testGetLogWillReturnSameInstance()
    {
        $p = "/some/path";
        $this->config->setLogPath($p);
        $ret1 = $this->backContainer->getLoggerInstance();
        $this->assertInstanceOf('ChristianBudde\Part\log\Logger', $ret1);
        $ret2 = $this->backContainer->getLoggerInstance();
        $this->assertTrue($ret1 === $ret2);
    }

    public function testGetLogWillReturnNullIfNoPathInConfig()
    {
        $ret1 = $this->backContainer->getLoggerInstance();
        $this->assertInstanceOf('ChristianBudde\Part\log\Logger', $ret1);
        $ret2 = $this->backContainer->getLoggerInstance();
        $this->assertTrue($ret1 === $ret2);

    }

    public function testGetMailDomainLibraryWillReturnNullIfNoPathInConfig()
    {
        $ret1 = $this->backContainer->getMailDomainLibraryInstance();
        $this->assertInstanceOf('ChristianBudde\Part\model\mail\DomainLibrary', $ret1);
        $ret2 = $this->backContainer->getMailDomainLibraryInstance();
        $this->assertTrue($ret1 === $ret2);

    }


    public function testMagicGetterWillGetOtherGetters()
    {
        $this->assertTrue($this->backContainer->getMailDomainLibraryInstance()  === $this->backContainer->mailDomainLibrary);
        $this->assertTrue($this->backContainer->getAJAXServerInstance()  === $this->backContainer->AJAXServer);
        $this->assertTrue($this->backContainer->getAJAXServerInstance()  === $this->backContainer->ajaxServer);
        $this->assertTrue($this->backContainer->getAJAXServerInstance()  === $this->backContainer->ajaxserver);
        $this->assertTrue($this->backContainer->getCacheControlInstance()  === $this->backContainer->cacheControl);
        $this->assertTrue($this->backContainer->getConfigInstance()  === $this->backContainer->config);
        $this->assertTrue($this->backContainer->getCSSRegisterInstance()  === $this->backContainer->CSSRegister);
        $this->assertTrue($this->backContainer->getCSSRegisterInstance()  === $this->backContainer->cssregister);
        $this->assertTrue($this->backContainer->getCurrentPageStrategyInstance()  === $this->backContainer->currentPageStrategy);
        $this->assertTrue($this->backContainer->getDartRegisterInstance()  === $this->backContainer->dartRegister);
        $this->assertTrue($this->backContainer->getDBInstance()  === $this->backContainer->db);
        $this->assertTrue($this->backContainer->getDefaultPageLibraryInstance()  === $this->backContainer->defaultPageLibrary);
        $this->assertTrue($this->backContainer->getFileLibraryInstance()  === $this->backContainer->fileLibrary);
        $this->assertTrue($this->backContainer->getJSRegisterInstance()  === $this->backContainer->JSRegister);
        $this->assertTrue($this->backContainer->getJSRegisterInstance()  === $this->backContainer->jsregister);
        $this->assertTrue($this->backContainer->getLoggerInstance()  === $this->backContainer->logger);
        $this->assertTrue($this->backContainer->getPageOrderInstance()  === $this->backContainer->pageOrder);
        $this->assertTrue($this->backContainer->getSiteInstance()  === $this->backContainer->site);
        $this->assertTrue($this->backContainer->getUpdaterInstance()  === $this->backContainer->updater);
        $this->assertTrue($this->backContainer->getUserLibraryInstance()  === $this->backContainer->userLibrary);

        $this->assertTrue(isset($this->backContainer->site));
    }

    public function testSetterWillNotChangeGetter(){
        $this->backContainer->updater = "test";
        $this->assertTrue($this->backContainer->getUpdaterInstance()  === $this->backContainer->updater);
    }


    public function testSetterWillSetNonReserved(){
        $this->backContainer->nonReserved = "test";
        $this->assertEquals('test', $this->backContainer->nonReserved);
    }

    public function testSetterIsCaseInsensitive(){
        $this->backContainer->nonReserved = "test";
        $this->backContainer->nonreserved = "test2";
        $this->assertEquals('test2', $this->backContainer->nonReserved);
    }


    public function testIssetWorks(){
        $this->assertFalse(isset($this->backContainer->nonReserved));
        $this->assertFalse(isset($this->backContainer->nonreserved));
        $this->backContainer->nonreserved = "test2";
        $this->assertTrue(isset($this->backContainer->nonreserved));
        $this->assertTrue(isset($this->backContainer->nonReserved));


    }

    public function testUnsetWorks(){
        $this->backContainer->nonreserved = "test2";
        $this->assertTrue(isset($this->backContainer->nonreserved));
        $this->assertTrue(isset($this->backContainer->nonReserved));
        unset($this->backContainer->nonreserved);
        $this->assertFalse(isset($this->backContainer->nonReserved));
        $this->assertFalse(isset($this->backContainer->nonreserved));

    }

    public function testSettingCallableWillCallCallable(){
        $this->backContainer->callable = function(BackendSingletonContainer $c){
            return $c;
        };
        $this->assertTrue($this->backContainer === $this->backContainer->callable);
    }

    public function testCallableValueIsCached(){
        $this->backContainer->callable = function(){
            return new StubConfigImpl();
        };
        $this->assertTrue($this->backContainer->callable === $this->backContainer->callable);

    }
}
