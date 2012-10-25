<?php
require_once dirname(__FILE__) . '/../_test/TruncateOperation.php';
require_once dirname(__FILE__) . '/../_test/_stub/StubDBImpl.php';
require_once dirname(__FILE__) . '/../_test/_stub/StubObserverImpl.php';
require_once dirname(__FILE__) . '/../_class/SiteImpl.php';
require_once dirname(__FILE__) . '/../_interface/PageOrder.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/16/12
 * Time: 7:57 PM
 */
class SiteImplTest extends PHPUnit_Extensions_Database_TestCase
{

    /** @var $db StubDBImpl */
    private $db;
    /** @var $pdo PDO */
    private $pdo;
    /** @var $site SiteImpl */
    private $site;
    /** @var $title string */
    private $title = 'testSite';

    public function setUp()
    {
        parent::setUp();
        $this->pdo = new PDO('mysql:dbname=' . self::database . ';host=' . self::host, self::username, self::password, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT=>true));
        $this->db = new StubDBImpl();
        $this->db->setConnection($this->pdo);
        $this->site = new SiteImpl($this->title, $this->db);
    }


    public function testGetTitleWillReturnTitleGivenInConstructor()
    {
        $title = $this->site->getTitle();
        $this->assertEquals($this->title, $title, 'The titles did not match');
    }


    public function testSetTitleWillSetTitleAndReturnTrue()
    {

        $newTitle = 'NewTitle';
        $this->assertNotEquals($this->title, $newTitle, 'Titles was equal');
        $ret = $this->site->setTitle($newTitle);
        $changedTitle = $this->site->getTitle();
        $this->assertEquals($newTitle, $changedTitle, 'The titles did not match');
        $this->assertTrue($ret, 'Did not return true on success');

    }


    public function testSetNotUniqueTitleWillReturnFalseAndNotChangeTitle()
    {
        $existingTitle = 'cms2012';
        $ret = $this->site->setTitle($existingTitle);
        $this->assertFalse($ret, 'Did not return false');
        $newTitle = $this->site->getTitle();
        $this->assertEquals($this->title, $newTitle, 'Titles did not match');
    }

    public function testTitleChangesWillBePersistent()
    {
        $existingTitle = 'cms2012';
        $newTitle = 'aNewTitle';
        $site = new SiteImpl($existingTitle, $this->db);
        $ret1 = $site->setTitle($newTitle);
        $this->assertTrue($ret1, 'Did not return true');

        $nonExistingTitle = 'nonExistingTitle';
        $newSite = new SiteImpl($nonExistingTitle, $this->db);
        $ret2 = $newSite->setTitle($newTitle);
        $this->assertFalse($ret2, 'Did not return false');

        $retTitle = $newSite->getTitle();
        $this->assertEquals($nonExistingTitle, $retTitle, 'Did change title');
    }


    public function testExistsWillReturnFalseIfNotExists()
    {
        $ret = $this->site->exists();
        $this->assertFalse($ret, 'Did not return false on not exists');
    }

    public function testExistsWillReturnTrueIfExists()
    {
        $site = new SiteImpl('cms2012', $this->db);
        $ret = $site->exists();
        $this->assertTrue($ret, 'Did not return true on exists ');
    }


    public function testCreateWillReturnFalseOnExistingSite()
    {
        $site = new SiteImpl('cms2012', $this->db);
        $ret = $site->exists();
        $this->assertTrue($ret, 'Did not return true on exists ');

        $ret2 = $site->create();
        $this->assertFalse($ret2, 'Did not return false on existing site');
    }

    public function testCreateWillReturnTrueOnSuccessfulCreate()
    {
        $ret = $this->site->create();
        $ret2 = $this->site->exists();
        $this->assertTrue($ret2, 'The site was not created');
        $this->assertTrue($ret, 'Create did not return true');

    }

    public function testDeleteWillReturnFalseIfSiteNotExists()
    {
        $ret = $this->site->delete();
        $this->assertFalse($ret, 'Delete did not return false on non exists');
    }

    public function testDeleteWillReturnTrueOnSuccessfulDeletion()
    {
        $site = new SiteImpl('cms2012', $this->db);
        $ret1 = $site->exists();
        $this->assertTrue($ret1, 'Did not exists');

        $ret2 = $site->delete();
        $ret3 = $site->exists();
        $this->assertFalse($ret3, 'Was not deleted');
        $this->assertTrue($ret2, 'Did not return true');
    }

    public function testGetterWillReflectPersistentStorageAfterInitialization()
    {
        $site = new SiteImpl('cms2012', $this->db);

        $db = $site->getDBDatabase();
        $host = $site->getDBHost();
        $password = $site->getDBPassword();
        $user = $site->getDBUser();

        $this->assertEquals('someDB', $db, 'Database did not match');
        $this->assertEquals('someHost', $host, 'Host did not match');
        $this->assertEquals('someUser', $user, 'User did not match');
    }


    public function testSettersWillSetValues()
    {

        $this->site->setDBDatabase('someDB');
        $this->site->setDBHost('someHost');
        $this->site->setDBPassword('somePass');
        $this->site->setDBUser('someUser');

        $db = $this->site->getDBDatabase();
        $host = $this->site->getDBHost();
        $password = $this->site->getDBPassword();
        $user = $this->site->getDBUser();

        $this->assertEquals('someDB', $db, 'Database did not match');
        $this->assertEquals('someHost', $host, 'Host did not match');
        $this->assertEquals('somePass', $password, 'Password did not match');
        $this->assertEquals('someUser', $user, 'User did not match');

    }

    public function testSettersWillBePersistent()
    {

        $this->assertFalse($this->site->exists(), 'Site did exists');
        $ret = $this->site->create();
        $this->assertTrue($ret, 'Create did fail');

        $this->site->setDBDatabase('someDB');
        $this->site->setDBHost('someHost');
        $this->site->setDBPassword('somePass');
        $this->site->setDBUser('someUser');

        $site = new SiteImpl($this->site->getTitle(), $this->db);
        $db = $site->getDBDatabase();
        $host = $site->getDBHost();
        $password = $site->getDBPassword();
        $user = $site->getDBUser();

        $this->assertTrue($site->exists(), 'Site did not exists');

        $this->assertEquals('someDB', $db, 'Database did not match');
        $this->assertEquals('someHost', $host, 'Host did not match');
        $this->assertEquals('somePass', $password, 'Password did not match');
        $this->assertEquals('someUser', $user, 'User did not match');
    }

    public function testGetPageOrderWillReturnFalseOnInvalidConnection()
    {
        $site = new SiteImpl('cms2012', $this->db);
        $ret = $site->getPageOrder();
        $this->assertFalse($ret);
    }

    public function testGetPageOrderWillReturnPageOrderOnValidConnection()
    {
        $this->site->setDBDatabase(self::database);
        $this->site->setDBHost(self::host);
        $this->site->setDBPassword(self::password);
        $this->site->setDBUser(self::username);

        $ret = $this->site->getPageOrder();

        $this->assertInstanceOf('PageOrder', $ret, 'Was not of right instance');
    }

    public function testChangeTitleWillCallObserver()
    {
        $site = new SiteImpl('cms2012', $this->db);
        $observer1 = new StubObserverImpl();
        $observer2 = new StubObserverImpl();
        $site->attachObserver($observer1);
        $site->attachObserver($observer2);

        $site->setTitle('someTitle');
        $this->assertTrue($observer1->hasBeenCalled());
        $this->assertTrue($observer2->hasBeenCalled());
        $this->assertTrue($observer1->getLastCallSubject() == $observer2->getLastCallSubject());
        $this->assertTrue($observer1->getLastCallType() == $observer2->getLastCallType());
        $this->assertTrue($observer1->getLastCallSubject() === $site);
        $this->assertTrue($observer1->getLastCallType() == Site::EVENT_TITLE_UPDATE);
    }

    public function testDetachObserverWillDetachObserver()
    {
        $site = new SiteImpl('cms2012', $this->db);
        $observer1 = new StubObserverImpl();
        $observer2 = new StubObserverImpl();
        $site->attachObserver($observer1);
        $site->attachObserver($observer2);
        $site->detachObserver($observer2);
        $ret = $site->setTitle('anotherTitle');
        $this->assertTrue($observer1->hasBeenCalled());
        $this->assertFalse($observer2->hasBeenCalled());
    }

    public function testDeleteWillCallObserver()
    {
        $site = new SiteImpl('cms2012', $this->db);
        $observer1 = new StubObserverImpl();
        $site->attachObserver($observer1);
        $site->delete();
        $this->assertTrue($observer1->hasBeenCalled());
        $this->assertTrue($site === $observer1->getLastCallSubject());
        $this->assertEquals(Site::EVENT_DELETE, $observer1->getLastCallType());
    }

    public function testCreateValidSiteWillReturnPageOrder(){
        $site = new SiteImpl('validSite',$this->db);
        $site->setDBDatabase(self::database);
        $site->setDBHost(self::host);
        $site->setDBUser(self::username);
        $site->setDBPassword(self::password);
        $ret = $site->create();
        $this->assertTrue($ret,'site was not created');
        $newSite = new SiteImpl('validSite',$this->db);
        $ret = $newSite->getPageOrder();
        $this->assertInstanceOf('PageOrder',$ret,'Did not return instance of PageOrder');
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
        return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/_mysqlXML/SiteImplTest.xml');
    }


    const database = 'cms2012testdb';
    const password = 'plovMand50';
    const username = 'cms2012';
    const host = '192.168.1.1';

}
