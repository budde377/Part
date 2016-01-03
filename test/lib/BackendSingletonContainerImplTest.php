<?php

/**
 * User: budde
 * Date: 6/13/12
 * Time: 4:59 PM
 */

namespace ChristianBudde\Part;

use ChristianBudde\Part\util\CustomDatabaseTestCase;

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
            'database' => self::$mysqlOptions->getDatabase(),
            'folders' =>  []);
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
    public function testGetTypeHandlerLibraryWillReturnSameSintacen()
    {
        $ret1 = $this->backContainer->getTypeHandlerLibraryInstance();
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\TypeHandlerLibraryImpl', $ret1);
        $ret2 = $this->backContainer->getTypeHandlerLibraryInstance();
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

    public function testGetDelayedExecutionQueueWillReturnSameInstance()
    {
        $ret1 = $this->backContainer->getDelayedExecutionTaskQueue();
        $this->assertInstanceOf('ChristianBudde\Part\util\task\TaskQueueImpl', $ret1);
        $ret2 = $this->backContainer->getDelayedExecutionTaskQueue();
        $this->assertTrue($ret1 === $ret2);
    }


    public function testMagicGetterWillGetOtherGetters()
    {
        $this->assertTrue($this->backContainer->getAJAXServerInstance()  === $this->backContainer->AJAXServer);
        $this->assertTrue($this->backContainer->getAJAXServerInstance()  === $this->backContainer->ajaxServer);
        $this->assertTrue($this->backContainer->getAJAXServerInstance()  === $this->backContainer->ajaxserver);
        $this->assertTrue($this->backContainer->getCacheControlInstance()  === $this->backContainer->cacheControl);
        $this->assertTrue($this->backContainer->getConfigInstance()  === $this->backContainer->config);
        $this->assertTrue($this->backContainer->getCurrentPageStrategyInstance()  === $this->backContainer->currentPageStrategy);
        $this->assertTrue($this->backContainer->getDBInstance()  === $this->backContainer->db);
        $this->assertTrue($this->backContainer->getDefaultPageLibraryInstance()  === $this->backContainer->defaultPageLibrary);
        $this->assertTrue($this->backContainer->getFileLibraryInstance()  === $this->backContainer->fileLibrary);
        $this->assertTrue($this->backContainer->getLoggerInstance()  === $this->backContainer->logger);
        $this->assertTrue($this->backContainer->getPageOrderInstance()  === $this->backContainer->pageOrder);
        $this->assertTrue($this->backContainer->getSiteInstance()  === $this->backContainer->site);
        $this->assertTrue($this->backContainer->getUpdaterInstance()  === $this->backContainer->updater);
        $this->assertTrue($this->backContainer->getUserLibraryInstance()  === $this->backContainer->userLibrary);
        $this->assertTrue($this->backContainer->getTmpFolderInstance()  === $this->backContainer->tmpfolder);
        $this->assertTrue($this->backContainer->getDelayedExecutionTaskQueue()  === $this->backContainer->delayedExecutionTaskQueue);

        $this->assertTrue(isset($this->backContainer->site));
        $this->assertTrue(isset($this->backContainer->tmpfolder));
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
        $this->assertEquals('test', $this->backContainer->nonreserved);
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

    public function testSetterCannotOverwrite(){
        $this->backContainer->v = 1;
        $this->backContainer->v = 2;
        $this->assertEquals(1, $this->backContainer->v);

    }


}
