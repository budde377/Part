<?php
require_once dirname(__FILE__) . '/../_test/TruncateOperation.php';
require_once dirname(__FILE__) . '/../_test/MySQLConstants.php';
require_once dirname(__FILE__) . '/../_test/_stub/StubDBImpl.php';
require_once dirname(__FILE__) . '/../_class/UserImpl.php';
require_once dirname(__FILE__) . '/../_class/MultiSiteUserPrivilegesImpl.php';
require_once dirname(__FILE__) . '/../_class/SiteLibraryImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 04/08/12
 * Time: 10:38
 */
class MultiSiteUserPrivilegesImplTest extends PHPUnit_Extensions_Database_TestCase
{
    /** @var $user StubUserImpl */
    private $user;
    /** @var $privileges MultiSiteUserPrivilegesImpl */
    private $privileges;
    /** @var $siteLibrary SiteLibraryImpl */
    private $siteLibrary;
    /** @var $pdo PDO */
    private $pdo;
    /** @var $db StubDBImpl */
    private $db;
    /** @var $site Site */
    private $site;
    /** @var $pageOrder PageOrder */
    private $pageOrder;
    /** @var $pages array */
    private $pages;

    protected function setUp()
    {
        parent::setUp();
        $this->pdo = new PDO('mysql:dbname=' . self::database . ';host=' . self::host, self::username, self::password, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT=>true));
        $this->db = new StubDBImpl();
        $this->db->setConnection($this->pdo);
        $this->user = new UserImpl('root', $this->db);
        $this->siteLibrary = new SiteLibraryImpl($this->db);
        $this->site = $this->siteLibrary->getSite('cms2012');

        $this->site->setDBDatabase(self::database);
        $this->site->setDBHost(self::host);
        $this->site->setDBUser(self::username);
        $this->site->setDBPassword(self::password);

        $this->pageOrder = $this->site->getPageOrder();
        $this->pages = $this->pageOrder->listPages(PageOrder::LIST_ALL);
        $this->privileges = new MultiSiteUserPrivilegesImpl($this->db, $this->user, $this->siteLibrary);
    }

    protected function tearDown()
    {
        $this->pdo = null;
        parent::tearDown();
    }

    public function testAddRootPrivilegeWillAddRootPrivilege()
    {
        $ret = $this->privileges->addRootPrivilege();
        $this->assertTrue($ret, 'Did not return true');
        $ret = $this->privileges->listPrivileges();
        $this->assertTrue(is_array($ret), 'Did not return array');
        $found = false;
        foreach ($ret as $privilege) {
            $found = (isset($privilege['type'])
                && $privilege['type'] == MultiSiteUserPrivileges::USER_PRIVILEGES_TYPE_ROOT
                && $privilege['site'] == null && $privilege['page'] == null) || $found;
        }

        $this->assertTrue($found, 'Root privilege was not added');
    }

    public function testAddRootPrivilegeCantBeDoneTwice()
    {
        $this->privileges->addRootPrivilege();
        $count1 = count($this->privileges->listPrivileges());
        $ret = $this->privileges->addRootPrivilege();
        $this->assertFalse($ret, 'Did not return false');
        $count2 = count($this->privileges->listPrivileges());

        $this->assertEquals($count1, $count2, 'Did add root privilege twice');
    }

    public function testAddSitePrivilegeWillAddSitePrivilege()
    {
        $ret = $this->privileges->addSitePrivilege($this->site->getTitle());
        $this->assertTrue($ret, 'Did not return true');
        $ret = $this->privileges->listPrivileges();
        $found = false;
        foreach ($ret as $privilege) {
            $found = (isset($privilege['type'])
                && $privilege['type'] == MultiSiteUserPrivileges::USER_PRIVILEGES_TYPE_SITE
                && $privilege['site'] == $this->site->getTitle() && $privilege['page'] == null) || $found;
        }

        $this->assertTrue($found, 'Root privilege was not added');

    }

    public function testAddNILSiteWillReturnFalse()
    {
        $count1 = count($this->privileges->listPrivileges());
        $ret = $this->privileges->addSitePrivilege('NonExistingSite');
        $this->assertFalse($ret, 'Did not return false');
        $count2 = count($this->privileges->listPrivileges());
        $this->assertEquals($count1, $count2, 'Did change privileges');
    }

    public function testAddPagePrivilegeWillAddPagePrivilege()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $ret = $this->privileges->addPagePrivilege($this->site->getTitle(), $page->getID());
        $this->assertTrue($ret, 'Did not return true');
        $ret = $this->privileges->listPrivileges();
        $found = false;
        foreach ($ret as $privilege) {
            $found = (isset($privilege['type'])
                && $privilege['type'] == MultiSiteUserPrivileges::USER_PRIVILEGES_TYPE_PAGE
                && $privilege['site'] == $this->site->getTitle()
                && $privilege['page'] == $page->getID()) || $found;
        }

        $this->assertTrue($found, 'Root privilege was not added');

    }

    public function testAddPagePrivilegeWillReturnFalseOnSiteNIL()
    {
        $count1 = count($this->privileges->listPrivileges());
        /** @var $page Page */
        $page = $this->pages[0];
        $ret = $this->privileges->addPagePrivilege('NonExistingSite', $page->getID());
        $this->assertFalse($ret, 'Did not return false');
        $count2 = count($this->privileges->listPrivileges());
        $this->assertEquals($count1, $count2, 'Did change privileges');
    }

    public function testAddPagePrivilegeWillReturnFalseOnPageNIL()
    {
        $count1 = count($this->privileges->listPrivileges());
        /** @var $page Page */
        $ret = $this->privileges->addPagePrivilege($this->site->getTitle(), 'NonExistingPage');
        $this->assertFalse($ret, 'Did not return false');
        $count2 = count($this->privileges->listPrivileges());
        $this->assertEquals($count1, $count2, 'Did change privileges');
    }

    public function testAddSitePrivilegeDuplicateWillReturnFalse()
    {
        $this->privileges->addSitePrivilege($this->site->getTitle());
        $count1 = count($this->privileges->listPrivileges());
        $ret = $this->privileges->addSitePrivilege($this->site->getTitle());
        $this->assertFalse($ret, 'Did not return false');
        $count2 = count($this->privileges->listPrivileges());
        $this->assertEquals($count1, $count2, 'Did change privileges');
    }

    public function testAddPagePrivilegeDuplicateWillReturnFalse()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $this->privileges->addPagePrivilege($this->site->getTitle(), $page->getID());
        $count1 = count($this->privileges->listPrivileges());
        $ret = $this->privileges->addPagePrivilege($this->site->getTitle(), $page->getID());
        $this->assertFalse($ret, 'Did not return false');
        $count2 = count($this->privileges->listPrivileges());
        $this->assertEquals($count1, $count2, 'Did change privileges');
    }

    public function testRevokeSitePrivilegeWillRevokePrivilege()
    {
        $this->privileges->addSitePrivilege($this->site->getTitle());
        $count1 = count($this->privileges->listPrivileges());
        $ret = $this->privileges->revokePrivilege($this->site->getTitle());
        $count2 = count($this->privileges->listPrivileges());
        $this->assertEquals($count1 - 1, $count2, 'Did not revoke privilege');
        $this->assertTrue($ret, 'Did not return true');
    }

    public function testRevokeRootPrivilegeWillRevokePrivilege()
    {
        $this->privileges->addRootPrivilege();
        $count1 = count($this->privileges->listPrivileges());
        $ret = $this->privileges->revokePrivilege();
        $count2 = count($this->privileges->listPrivileges());
        $this->assertEquals($count1 - 1, $count2, 'Did not revoke privilege');
        $this->assertTrue($ret, 'Did not return true');
    }


    public function testRevokePagePrivilegeWillRevokePrivilege()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $this->privileges->addPagePrivilege($this->site->getTitle(), $page->getID());
        $count1 = count($this->privileges->listPrivileges());
        $ret = $this->privileges->revokePrivilege($this->site->getTitle(), $page->getID());
        $count2 = count($this->privileges->listPrivileges());
        $this->assertEquals($count1 - 1, $count2, 'Did not revoke privilege');
        $this->assertTrue($ret, 'Did not return true');
    }

    public function testRevokeInvalidPermissionWillReturnFalse()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $this->assertFalse($this->privileges->revokePrivilege(null, $page->getID()), 'Did not return false');
    }

    public function testRevokePermissionNotFoundWillReturnFalse()
    {
        $this->assertFalse($this->privileges->revokePrivilege(), 'Did not return false');
        $this->assertFalse($this->privileges->revokePrivilege('NILSite'), 'Did not return false');
        $this->assertFalse($this->privileges->revokePrivilege('NILSite', 'NILPage'), 'Did not return false');
    }

    public function testIsPrivilegedWillreturnFalseIfInvalidPermission()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $this->assertFalse($this->privileges->isPrivileged(null, $page->getID()), 'Did not return false');
    }

    public function testIsPrivilegedWillReturnTrueIfPagePrivileged()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $this->privileges->addPagePrivilege($this->site->getTitle(), $page->getID());
        $this->assertTrue($this->privileges->isPrivileged($this->site->getTitle(), $page->getID()));
        $this->assertFalse($this->privileges->isPrivileged($this->site->getTitle()));
        $this->assertFalse($this->privileges->isPrivileged());


    }

    public function testIsPrivilegedWillReturnTrueIfSitePrivileged()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $this->privileges->addSitePrivilege($this->site->getTitle());
        $this->assertTrue($this->privileges->isPrivileged($this->site->getTitle(), $page->getID()));
        $this->assertTrue($this->privileges->isPrivileged($this->site->getTitle()));
        $this->assertFalse($this->privileges->isPrivileged());


    }

    public function testIsPrivilegedWillReturnTrueIfRootPrivileged()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $this->privileges->addRootPrivilege();
        $this->assertTrue($this->privileges->isPrivileged($this->site->getTitle(), $page->getID()));
        $this->assertTrue($this->privileges->isPrivileged($this->site->getTitle()));
        $this->assertTrue($this->privileges->isPrivileged());

    }

    public function testChangesArePersistent()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $this->privileges->addRootPrivilege();
        $this->privileges->addSitePrivilege($this->site->getTitle());
        $this->privileges->addPagePrivilege($this->site->getTitle(), $page->getID());

        $this->privileges = new MultiSiteUserPrivilegesImpl($this->db, $this->user, $this->siteLibrary);
        $this->assertEquals(3, count($this->privileges->listPrivileges()), 'Count did not match');
        $this->assertTrue($this->privileges->revokePrivilege(), 'Root privileges was not persistent');
        $this->assertTrue($this->privileges->revokePrivilege($this->site->getTitle()), 'Page privileges was not persistent');
        $this->assertTrue($this->privileges->revokePrivilege($this->site->getTitle(), $page->getID()), 'Site privileges was not persistent');
    }


    public function testDeletesWillReflectOnPersistentStorage()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $this->privileges->addRootPrivilege();
        $this->privileges->addSitePrivilege($this->site->getTitle());
        $this->privileges->addPagePrivilege($this->site->getTitle(), $page->getID());
        $this->assertEquals(3, count($this->privileges->listPrivileges()), 'Count did not match');

        $this->privileges->revokePrivilege();
        $this->privileges->revokePrivilege($this->site->getTitle());
        $this->privileges->revokePrivilege($this->site->getTitle(), $page->getID());

        $this->privileges = new MultiSiteUserPrivilegesImpl($this->db, $this->user, $this->siteLibrary);
        $this->assertEquals(0, count($this->privileges->listPrivileges()), 'Count did not match');


    }

    public function testIsPrivilegedWillReflectPersistentStorage()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $this->privileges->addRootPrivilege();
        $this->privileges->addSitePrivilege($this->site->getTitle());
        $this->privileges->addPagePrivilege($this->site->getTitle(), $page->getID());
        $this->assertEquals(3, count($this->privileges->listPrivileges()), 'Count did not match');

        $this->privileges = new MultiSiteUserPrivilegesImpl($this->db, $this->user, $this->siteLibrary);
        $this->assertTrue($this->privileges->isPrivileged());
        $this->assertTrue($this->privileges->isPrivileged($this->site->getTitle()));
        $this->assertTrue($this->privileges->isPrivileged($this->site->getTitle(), $page->getID()));


    }

    public function testRevokeAllWillRevokeAll()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $this->privileges->addRootPrivilege();
        $this->privileges->addSitePrivilege($this->site->getTitle());
        $this->privileges->addPagePrivilege($this->site->getTitle(), $page->getID());
        $this->assertEquals(3, count($this->privileges->listPrivileges()), 'Count did not match');

        $this->privileges->revokeAllPrivileges();
        $this->assertEquals(0, count($this->privileges->listPrivileges()), 'Count did not match');

        $this->privileges = new MultiSiteUserPrivilegesImpl($this->db, $this->user, $this->siteLibrary);
        $this->assertEquals(0, count($this->privileges->listPrivileges()), 'Count did not match');


    }

    public function testListModeWillReflectOnListPrivileges()
    {
        /** @var $page Page */
        $page = $this->pages[0];
        $this->privileges->addRootPrivilege();
        $this->privileges->addSitePrivilege($this->site->getTitle());
        $this->privileges->addPagePrivilege($this->site->getTitle(), $page->getID());


        $this->assertEquals(3, count($this->privileges->listPrivileges()), 'Count did not match');
        $this->assertEquals(1, count($this->privileges->listPrivileges(MultiSiteUserPrivileges::LIST_MODE_LIST_PAGE)), 'Count did not match');
        $this->assertEquals(1, count($this->privileges->listPrivileges(MultiSiteUserPrivileges::LIST_MODE_LIST_SITE)), 'Count did not match');
        $this->assertEquals(1, count($this->privileges->listPrivileges(MultiSiteUserPrivileges::LIST_MODE_LIST_ROOT)), 'Count did not match');


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
        return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/_mysqlXML/MultiSiteUserPrivilegesImplTest.xml');
    }

    const database = MySQLConstants::MYSQL_DATABASE;
    const password = MySQLConstants::MYSQL_PASSWORD;
    const username = MySQLConstants::MYSQL_USERNAME;
    const host = MySQLConstants::MYSQL_HOST;


}
