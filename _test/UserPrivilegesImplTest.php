<?php
require_once dirname(__FILE__) . '/../_test/TruncateOperation.php';
require_once dirname(__FILE__) . '/../_test/MySQLConstants.php';
require_once dirname(__FILE__) . '/_stub/StubDBImpl.php';
require_once dirname(__FILE__) . '/../_class/UserImpl.php';
require_once dirname(__FILE__) . '/../_class/PageImpl.php';
require_once dirname(__FILE__) . '/../_class/UserPrivilegesImpl.php';
require_once dirname(__FILE__) . '/_stub/StubPageImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 19/01/13
 * Time: 10:32
 */
class UserPrivilegesImplTest extends PHPUnit_Extensions_Database_TestCase
{

    /** @var User */
    private $user;
    /** @var UserPrivilegesImpl */
    private $userPrivileges;
    /** @var PDO */
    private $pdo;
    /** @var DB */
    private $db;
    /** @var Page */
    private $page1;
    /** @var $page2 */
    private $page2;

    public function setUp()
    {
        parent::setUp();
        $this->pdo = new PDO('mysql:dbname=' . self::database . ';host=' . self::host, self::username, self::password, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true));
        $this->db = new StubDBImpl();
        $this->db->setConnection($this->pdo);
        $this->user = new UserImpl('root', $this->db);
        $this->page1 = new PageImpl('page', $this->db);
        $this->page2 = new PageImpl('page2', $this->db);
        $this->userPrivileges = new UserPrivilegesImpl($this->user, $this->db);

    }

    private function setUpAllPrivileges()
    {
        $this->userPrivileges->addRootPrivileges();
        $this->userPrivileges->addSitePrivileges();
        $this->userPrivileges->addPagePrivileges($this->page1);
        $this->userPrivileges->addPagePrivileges($this->page2);

    }


    public function testAddersWillSet()
    {
        $this->setUpAllPrivileges();
        $this->assertTrue($this->userPrivileges->hasRootPrivileges());
        $this->assertTrue($this->userPrivileges->hasSitePrivileges());
        $this->assertTrue($this->userPrivileges->hasPagePrivileges($this->page1));
        $this->assertTrue($this->userPrivileges->hasPagePrivileges($this->page2));

    }

    public function testRevokeAllPrivilegesRevokeAllPrivileges()
    {
        $this->setUpAllPrivileges();
        $this->userPrivileges->revokeAllPrivileges();
        $this->assertFalse($this->userPrivileges->hasRootPrivileges());
        $this->assertFalse($this->userPrivileges->hasSitePrivileges());
        $this->assertFalse($this->userPrivileges->hasPagePrivileges($this->page1));
        $this->assertFalse($this->userPrivileges->hasPagePrivileges($this->page2));
    }

    public function testRemoveWillRemovePrivileges()
    {
        $this->setUpAllPrivileges();
        $this->userPrivileges->revokeRootPrivileges();
        $this->userPrivileges->revokeSitePrivileges();
        $this->userPrivileges->revokePagePrivileges($this->page2);
        $this->assertFalse($this->userPrivileges->hasRootPrivileges());
        $this->assertFalse($this->userPrivileges->hasSitePrivileges());
        $this->assertTrue($this->userPrivileges->hasPagePrivileges($this->page1));
        $this->assertFalse($this->userPrivileges->hasPagePrivileges($this->page2));
    }

    public function testRootAccessWillGrantSiteAndPageAccess()
    {
        $this->userPrivileges->addRootPrivileges();
        $this->assertTrue($this->userPrivileges->hasRootPrivileges());
        $this->assertTrue($this->userPrivileges->hasSitePrivileges());
        $this->assertTrue($this->userPrivileges->hasPagePrivileges($this->page1));
        $this->assertTrue($this->userPrivileges->hasPagePrivileges($this->page2));
    }

    public function testSiteAccessWillGrantPageAccess()
    {
        $this->userPrivileges->addSitePrivileges();
        $this->assertFalse($this->userPrivileges->hasRootPrivileges());
        $this->assertTrue($this->userPrivileges->hasSitePrivileges());
        $this->assertTrue($this->userPrivileges->hasPagePrivileges($this->page1));
        $this->assertTrue($this->userPrivileges->hasPagePrivileges($this->page2));
    }

    public function testAddRootWillBePersistent()
    {
        $this->userPrivileges->addRootPrivileges();
        $this->userPrivileges = new UserPrivilegesImpl($this->user, $this->db);
        $this->assertTrue($this->userPrivileges->hasRootPrivileges());
    }

    public function testRemoveRootWillBePersistent()
    {
        $this->userPrivileges->addRootPrivileges();
        $this->userPrivileges->revokeRootPrivileges();
        $this->userPrivileges = new UserPrivilegesImpl($this->user, $this->db);
        $this->assertFalse($this->userPrivileges->hasRootPrivileges());
    }

    public function testAddSitePrivilegesWillBePersistent()
    {
        $this->userPrivileges->addSitePrivileges();
        $this->userPrivileges = new UserPrivilegesImpl($this->user, $this->db);
        $this->assertTrue($this->userPrivileges->hasSitePrivileges());
    }

    public function testRemoveSitePrivilegesWillBePersistent()
    {
        $this->userPrivileges->addSitePrivileges();
        $this->userPrivileges->revokeSitePrivileges();
        $this->userPrivileges = new UserPrivilegesImpl($this->user, $this->db);
        $this->assertFalse($this->userPrivileges->hasSitePrivileges());
    }

    public function testAddPagePrivilegesWillBePersistent()
    {
        $this->userPrivileges->addPagePrivileges($this->page1);
        $this->userPrivileges = new UserPrivilegesImpl($this->user, $this->db);
        $this->assertTrue($this->userPrivileges->hasPagePrivileges($this->page1));
    }


    public function testRemovePagePrivilegesWillBePersistent()
    {
        $this->userPrivileges->addPagePrivileges($this->page1);
        $this->userPrivileges->revokePagePrivileges($this->page1);
        $this->userPrivileges = new UserPrivilegesImpl($this->user, $this->db);
        $this->assertFalse($this->userPrivileges->hasPagePrivileges($this->page1));
    }

    public function testRevokeAllPrivilegesWillBePersistent()
    {
        $this->setUpAllPrivileges();
        $this->userPrivileges->revokeAllPrivileges();
        $this->userPrivileges = new UserPrivilegesImpl($this->user, $this->db);
        $this->assertFalse($this->userPrivileges->hasRootPrivileges());
        $this->assertFalse($this->userPrivileges->hasSitePrivileges());
        $this->assertFalse($this->userPrivileges->hasPagePrivileges($this->page1));
        $this->assertFalse($this->userPrivileges->hasPagePrivileges($this->page2));
    }

    public function testAddNonExistingPageWillNotAddPrivilege()
    {
        $pageStub = new StubPageImpl();
        $pageStub->setID("testID");
        $this->userPrivileges->addPagePrivileges($pageStub);
        $this->assertFalse($this->userPrivileges->hasPagePrivileges($pageStub));
    }

    public function testUserPrivilegesChangesWillBePersistentAfterChangeOfUsername()
    {
        $this->assertFalse($this->userPrivileges->hasRootPrivileges());
        $this->user->setUsername('someOtherValidUsername');
        $this->userPrivileges->addRootPrivileges();
        $this->assertTrue($this->userPrivileges->hasRootPrivileges());
        $userPrivileges = new UserPrivilegesImpl($this->user, $this->db);
        $this->assertTrue($userPrivileges->hasRootPrivileges());
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
        return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/_mysqlXML/UserPrivilegesImplTest.xml');
    }

    const database = MySQLConstants::MYSQL_DATABASE;
    const password = MySQLConstants::MYSQL_PASSWORD;
    const username = MySQLConstants::MYSQL_USERNAME;
    const host = MySQLConstants::MYSQL_HOST;
}
