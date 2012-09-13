<?php
require_once dirname(__FILE__) . '/../_test/TruncateOperation.php';
require_once dirname(__FILE__) . '/../_test/_stub/StubDBImpl.php';
require_once dirname(__FILE__) . '/../_class/SiteLibraryImpl.php';
require_once dirname(__FILE__) . '/../_class/SiteImpl.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/16/12
 * Time: 11:13 PM
 */
class SiteLibraryImplTest extends PHPUnit_Extensions_Database_TestCase
{
    /** @var $db StubDBImpl */
    private $db;
    /** @var $pdo PDO */
    private $pdo;
    /** @var $siteLibrary SiteLibraryImpl */
    private $siteLibrary;

    public function testListLibraryWillReturnArrayCorrespondingToDatabase()
    {
        $ret = $this->siteLibrary->listSites();
        $this->assertArrayHasKey(0, $ret, 'Did not have right entrance');
        $this->assertInstanceOf('Site', $ret[0], 'Was not of right instance');
        /** @var $site Site */
        $site = $ret[0];
        $ret = $site->getTitle();
        $this->assertEquals('cms2012', $ret, 'Did not return right title');
    }

    public function testDeleteWillDeleteSiteFromList()
    {
        $ret = $this->siteLibrary->listSites();
        /** @var $site Site */
        $site = $ret[0];
        $ret = $this->siteLibrary->deleteSite($site);
        $this->assertTrue($ret, 'Delete did not return TRUE');
        $ret = $this->siteLibrary->listSites();
        $this->assertTrue(is_array($ret), 'Did not return array');
        $this->assertEquals(0, count($ret), 'Did not return array of right length');

    }

    public function testDeleteWithNILSiteWithWrongTitleWillReturnFalse()
    {
        $site = new SiteImpl('test', $this->db);
        $ret = $this->siteLibrary->deleteSite($site);
        $this->assertFalse($ret, 'Did not return FALSE');
    }

    public function testDeleteWithNILSiteWithExistingTitleWillReturnFalse()
    {
        $site = new SiteImpl('cms2012', $this->db);
        $ret = $this->siteLibrary->deleteSite($site);
        $this->assertFalse($ret, 'Did not return FALSE');
    }

    public function testDeleteWillDeleteFromPersistenStorage()
    {
        $ret = $this->siteLibrary->listSites();
        /** @var $site Site */
        $site = $ret[0];
        $ret = $this->siteLibrary->deleteSite($site);
        $this->assertTrue($ret, 'Delete did not return TRUE');

        $library = new SiteLibraryImpl($this->db);
        $ret = $library->listSites();
        $this->assertEquals(0, count($ret), 'Did not delete from persistent');

    }

    public function testCreateWillReturnFalseOnCreatingExisting()
    {
        $ret = $this->siteLibrary->createSite('cms2012');
        $this->assertFalse($ret, 'Did not return false');
    }

    public function testCreateWillReturnSiteOnNonExistingTitle()
    {
        $ret = $this->siteLibrary->createSite('testTitle');
        $this->assertInstanceOf('Site', $ret, 'Did not return right instance');
        $this->assertEquals($ret->getTitle(), 'testTitle', 'Did not return instance with right title');
        $ret = $ret->exists();
        $this->assertTrue($ret, 'Did not return true');
    }

    public function testCreateWillAddSiteToList()
    {
        $this->siteLibrary->createSite('testTitle');
        $ret = $this->siteLibrary->listSites();
        $this->assertEquals(2, count($ret), 'Did not add site');
    }

    public function testDeleteOnObjectWillReflectOnLibrary()
    {
        $ret = $this->siteLibrary->listSites();
        /** @var $site Site */
        $site = $ret[0];
        $ret = $site->delete();
        $this->assertTrue($ret, 'Did not return true');
        $ret = $this->siteLibrary->listSites();
        $this->assertEquals(0, count($ret), 'Did not return array of right length');
    }

    public function testWillNotifyCreatedSiteDelete()
    {
        $ret = $this->siteLibrary->createSite('someTitle');
        $this->assertInstanceOf('Site', $ret);
        $ret->delete();
        $ret = $this->siteLibrary->listSites();
        $this->assertEquals(1, count($ret), 'Was not of right length');
    }

    public function testExternalChangeOfTitleWillAllowDeletion()
    {
        $ret = $this->siteLibrary->createSite('someTitle');
        $ret->setTitle('SomeOtherTitle');
        $ret = $this->siteLibrary->deleteSite($ret);
        $this->assertTrue($ret);
    }

    public function testGetSiteWillReturnSite(){
        $ret = $this->siteLibrary->getSite('cms2012');
        $this->assertInstanceOf('Site',$ret,'Did not return instance of Site');
        $this->assertEquals('cms2012',$ret->getTitle(),'Titles did not match');
    }

    public function testGetSiteWillReturnNullOnSiteNIL(){
        $ret = $this->siteLibrary->getSite('NonExistingSite');
        $this->assertNull($ret,'Did not return null');
    }

    public function testListUsersAndIteratorWillMatch(){
        $list = $this->siteLibrary->listSites();
        foreach($this->siteLibrary as $key=>$users){
            $this->assertTrue(isset($list[$key]),'Key was not found');
            $this->assertTrue($list[$key] === $users,'Users did not match');
            unset($list[$key]);
        }
        $this->assertEquals(0,count($list),'List was not covered');
    }

    public function testListUsersAndIteratorWillMatchAfterDelete(){
        $site = $this->siteLibrary->getSite('cms2012');
        $this->siteLibrary->deleteSite($site);
        $list = $this->siteLibrary->listSites();
        foreach($this->siteLibrary as $key=>$users){
            $this->assertTrue(isset($list[$key]),'Key was not found');
            $this->assertTrue($list[$key] === $users,'Users did not match');
            unset($list[$key]);
        }
        $this->assertEquals(0,count($list),'List was not covered');


    }

    public function testListUsersAndIteratorWillMatchAfterRemoteDelete(){
        $site = $this->siteLibrary->getSite('cms2012');
        $site->delete();
        $list = $this->siteLibrary->listSites();
        foreach($this->siteLibrary as $key=>$users){
            $this->assertTrue(isset($list[$key]),'Key was not found');
            $this->assertTrue($list[$key] === $users,'Users did not match');
            unset($list[$key]);
        }
        $this->assertEquals(0,count($list),'List was not covered');


    }

    public function testListUsersAndIteratorWillMatchAfterCreate(){
        $this->siteLibrary->createSite('site1233312');
        $list = $this->siteLibrary->listSites();
        foreach($this->siteLibrary as $key=>$users){
            $this->assertTrue(isset($list[$key]),'Key was not found');
            $this->assertTrue($list[$key] === $users,'Users did not match');
            unset($list[$key]);
        }
        $this->assertEquals(0,count($list),'List was not covered');


    }

    public function setUp()
    {
        parent::setUp();
        $this->pdo = new PDO('mysql:dbname=' . self::database . ';host=' . self::host, self::username, self::password, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT=>true));
        $this->db = new StubDBImpl();
        $this->db->setConnection($this->pdo);
        $this->siteLibrary = new SiteLibraryImpl($this->db);
    }


    public function getSetUpOperation()
    {
        $cascadeTruncates = true;
        return new PHPUnit_Extensions_Database_Operation_Composite(array(new TruncateOperation($cascadeTruncates), PHPUnit_Extensions_Database_Operation_Factory::INSERT()));
    }

    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        $pdo = new PDO('mysql:dbname=' . self::database . ';host=' . self::host, self::username, self::password);
        return $this->createDefaultDBConnection($pdo);
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/_mysqlXML/SiteLibraryImplTest.xml');
    }


    const database = 'cms2012testdb';
    const password = 'plovMand50';
    const username = 'cms2012';
    const host = '192.168.1.1';
}
