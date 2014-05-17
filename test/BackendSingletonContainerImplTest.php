<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 4:59 PM
 * To change this template use File | Settings | File Templates.
 */
class BackendSingletonContainerImplTest extends PHPUnit_Framework_TestCase
{

    /** @var $config StubConfigImpl */
    private $config;
    /** @var $backContainer BackendSingletonContainerImpl*/
    private $backContainer;

    protected function setUp()
    {
        $this->config = new StubConfigImpl();
        $this->config->setMysqlCon(array('host'=>MySQLConstants::MYSQL_HOST, 'user'=>MySQLConstants::MYSQL_USERNAME, 'password'=>MySQLConstants::MYSQL_PASSWORD, 'database'=>MySQLConstants::MYSQL_DATABASE));
        $this->backContainer = new BackendSingletonContainerImpl($this->config);
    }

    public function testGetDBInstanceReturnsSameInstanceOfDB()
    {
        $db1 = $this->backContainer->getDBInstance();
        $this->assertInstanceOf('DB', $db1, 'Did not return instance of DB');
        $this->config->setMysqlCon(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $db2 = $this->backContainer->getDBInstance();
        $this->assertTrue($db1 == $db2, 'Did not reuse instance');
    }

    public function testGetCSSRegisterReturnsSameInstanceOfCSSRegister()
    {
        $css1 = $this->backContainer->getCSSRegisterInstance();
        $this->assertInstanceOf('CSSRegister', $css1, 'Did not return instance of CSSRegister');
        $this->config->setMysqlCon(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $css2 = $this->backContainer->getCSSRegisterInstance();
        $this->assertTrue($css1 === $css2, 'Did not reuse instance');

    }

    public function testGetDartRegisterReturnsSameInstanceOfDartRegister()
    {
        $dart1 = $this->backContainer->getDartRegisterInstance();
        $this->assertInstanceOf('DartRegister', $dart1, 'Did not return instance of CSSRegister');
        $this->config->setMysqlCon(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $dart2 = $this->backContainer->getDartRegisterInstance();
        $this->assertTrue($dart1 === $dart2, 'Did not reuse instance');

    }

    public function testGetJSRegisterReturnsSameInstanceOfJSRegister()
    {
        $js1 = $this->backContainer->getJSRegisterInstance();
        $this->assertInstanceOf('JSRegister', $js1, 'Did not return instance of JSRegister');
        $this->config->setMysqlCon(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $js2 = $this->backContainer->getJSRegisterInstance();
        $this->assertTrue($js1 === $js2, 'Did not reuse instance');

    }

    public function testGetAJAXRegisterReturnsSameInstanceOfAJAXRegister()
    {
        $ajax1 = $this->backContainer->getAJAXRegisterInstance();
        $this->assertInstanceOf('AJAXRegister', $ajax1, 'Did not return instance of AJAXRegister');
        $this->config->setMysqlCon(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $ajax2 = $this->backContainer->getAJAXRegisterInstance();
        $this->assertTrue($ajax1 === $ajax2, 'Did not reuse instance');

    }

    public function testGetPageOrderRegisterReturnsSameInstanceOfPageOrder()
    {
        $this->config->setMysqlCon(array('host' => MySQLConstants::MYSQL_HOST,
            'user' => MySQLConstants::MYSQL_USERNAME,
            'database' => MySQLConstants::MYSQL_DATABASE,
            'password' => MySQLConstants::MYSQL_PASSWORD));
        $pageOrder1 = $this->backContainer->getPageOrderInstance();
        $this->assertInstanceOf('PageOrder', $pageOrder1, 'Did not return instance of PageOrder');
        $pageOrder2 = $this->backContainer->getPageOrderInstance();
        $this->assertTrue($pageOrder1 === $pageOrder2, 'Did not reuse instance');

    }

    public function testGetCurrentPageStrategyReturnsSameInstanceOfCurrentPageStrategy()
    {
        $this->config->setMysqlCon(array('host' => MySQLConstants::MYSQL_HOST,
            'user' => MySQLConstants::MYSQL_USERNAME,
            'database' => MySQLConstants::MYSQL_DATABASE,
            'password' => MySQLConstants::MYSQL_PASSWORD));
        $pageOrder1 = $this->backContainer->getCurrentPageStrategyInstance();
        $this->assertInstanceOf('CurrentPageStrategy', $pageOrder1, 'Did not return instance of CurrentPageStrategy');
        $pageOrder2 = $this->backContainer->getCurrentPageStrategyInstance();
        $this->assertTrue($pageOrder1 === $pageOrder2, 'Did not reuse instance');


    }

    public function testGetUserLibraryInstanceReturnsSameInstanceOfUserLibrary()
    {
        $this->config->setMysqlCon(array('host' => MySQLConstants::MYSQL_HOST,
            'user' => MySQLConstants::MYSQL_USERNAME,
            'database' => MySQLConstants::MYSQL_DATABASE,
            'password' => MySQLConstants::MYSQL_PASSWORD));
        $ret1 = $this->backContainer->getUserLibraryInstance();
        $this->assertInstanceOf('UserLibrary', $ret1, 'Did not return instance of SiteLibrary');
        $ret2 = $this->backContainer->getUserLibraryInstance();
        $this->assertTrue($ret1 === $ret2, 'Did not reuse instance');


    }



    public function testGetConfigInstanceWillReturnSameInstanceOfConfig()
    {
        $ret1 = $this->backContainer->getConfigInstance();
        $this->assertInstanceOf('Config', $ret1, 'Did not return instance of Config');
        //$this->config->setMysqlCon(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $ret2 = $this->backContainer->getConfigInstance();
        $this->assertTrue($ret1 === $ret2, 'Did not reuse instance');

    }
    public function testGetDefaultPageLibraryWillReturnASameInstanceOfDefaultPageLibrary()
    {
        $ret1 = $this->backContainer->getDefaultPageLibraryInstance();
        $this->assertInstanceOf('DefaultPageLibrary', $ret1, 'Did not return instance of DefaultPageLibrary');
        //$this->config->setMysqlCon(array('host' => 'lol', 'user' => 'lol', 'database' => '', 'password' => ''));
        $ret2 = $this->backContainer->getDefaultPageLibraryInstance();
        $this->assertTrue($ret1 === $ret2, 'Did not reuse instance');

    }

    public function testGetCacheControlWillReturnSameInstanceOfCacheControl(){
        $ret1 = $this->backContainer->getCacheControlInstance();
        $this->assertInstanceOf('CacheControl',$ret1);
        $ret2 =$this->backContainer->getCacheControlInstance();
        $this->assertTrue($ret1 === $ret2);
    }

    public function testGetUpdaterWillReturnSameInstanceOfUpdater(){
        $ret1 = $this->backContainer->getUpdater();
        $this->assertInstanceOf('Updater',$ret1);
        $ret2 =$this->backContainer->getUpdater();
        $this->assertTrue($ret1 === $ret2);
    }

    public function testGetSiteVariablesWillReturnSameInstanceOfUpdater(){
        $ret1 = $this->backContainer->getSiteInstance();
        $this->assertInstanceOf('Site',$ret1);
        $ret2 =$this->backContainer->getSiteInstance();
        $this->assertTrue($ret1 === $ret2);
    }

    public function testGetFileLibraryWillReturnSameInstance(){
        $ret1 = $this->backContainer->getFileLibraryInstance();
        $this->assertInstanceOf('FileLibrary',$ret1);
        $ret2 =$this->backContainer->getFileLibraryInstance();
        $this->assertTrue($ret1 === $ret2);
    }

    public function testGetLogWillReturnSameInstance(){
        $p = "/some/path";
        $this->config->setLogPath($p);
        $ret1 = $this->backContainer->getLogInstance();
        $this->assertInstanceOf('LogFile',$ret1);
        $ret2 =$this->backContainer->getLogInstance();
        $this->assertTrue($ret1 === $ret2);
        $this->assertEquals($ret1->getAbsoluteFilePath(), $p);
    }

    public function testGetLogWillReturnNullIfNoPathInConfig(){
        $ret1 = $this->backContainer->getLogInstance();
        $this->assertInstanceOf('LogFile',$ret1);
        $ret2 =$this->backContainer->getLogInstance();
        $this->assertTrue($ret1 === $ret2);

    }

}
