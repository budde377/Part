<?php
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\model\user\UserPrivilegesImpl;
use ChristianBudde\cbweb\model\user\UserImpl;
use ChristianBudde\cbweb\model\page\PageImpl;
use ChristianBudde\cbweb\controller\json\UserPrivilegesJSONObjectImpl;
use ChristianBudde\cbweb\test\util\CustomDatabaseTestCase;
use ChristianBudde\cbweb\test\stub\StubDBImpl;
use ChristianBudde\cbweb\test\stub\StubPageImpl;
use ChristianBudde\cbweb\test\stub\StubPageOrderImpl;
use ChristianBudde\cbweb\util\db\DB;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 19/01/13
 * Time: 10:32
 */
class UserPrivilegesImplTest extends CustomDatabaseTestCase
{

    /** @var \ChristianBudde\cbweb\model\user\User */
    private $user;
    /** @var UserPrivilegesImpl */
    private $userPrivileges;
    /** @var DB */
    private $db;
    /** @var \ChristianBudde\cbweb\model\page\Page */
    private $page1;
    /** @var $page2 */
    private $page2;

    /** @var  StubPageOrderImpl */
    private $pageLibrary;

    function __construct($dataset = null)
    {
        parent::__construct(dirname(__FILE__) . '/mysqlXML/UserPrivilegesImplTest.xml');
    }


    public function setUp()
    {
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->user = new UserImpl('root', $this->db);
        $this->page1 = new PageImpl('page', $this->db);
        $this->page2 = new PageImpl('page2', $this->db);
        $this->userPrivileges = new UserPrivilegesImpl($this->user, $this->db);

        $this->pageLibrary = new StubPageOrderImpl();
        $this->pageLibrary->setOrder(array(null => array($this->page1, $this->page2)));
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

    public function testListPagePrivilegesWillListIdAsString()
    {
        $this->userPrivileges->addPagePrivileges($this->page1);
        $array = $this->userPrivileges->listPagePrivileges();
        $this->assertTrue(is_array($array));
        $this->assertEquals(1, count($array));
        $this->assertArrayHasKey(0, $array);
        $this->assertEquals($this->page1->getID(), $array[0]);
    }

    public function testListPagePrivilegesWillReturnEmptyArrayIfRoot()
    {
        $this->userPrivileges->addPagePrivileges($this->page1);
        $this->userPrivileges->addRootPrivileges();
        $array = $this->userPrivileges->listPagePrivileges();
        $this->assertEquals(0, count($array));
    }

    public function testListPagePrivilegesWillReturnEmptyArrayIfSiteAdmin()
    {
        $this->userPrivileges->addPagePrivileges($this->page1);
        $this->userPrivileges->addSitePrivileges();
        $array = $this->userPrivileges->listPagePrivileges();
        $this->assertEquals(0, count($array));
    }

    public function testListPagePrivilegesWillReturnRightInstanceIfGivenPageLibrary()
    {
        $this->userPrivileges->addPagePrivileges($this->page1);
        $array = $this->userPrivileges->listPagePrivileges($this->pageLibrary);
        $this->assertTrue(is_array($array));
        $this->assertEquals(1, count($array));
        $this->assertArrayHasKey(0, $array);
        $this->assertTrue($this->page1 === $array[0]);
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

    public function testPrivilegesIsJSONObjectSerializable()
    {
        $o = $this->userPrivileges->jsonObjectSerialize();
        $this->assertInstanceOf('ChristianBudde\cbweb\UserPrivilegesJSONObjectImpl', $o);
        $this->assertEquals(new UserPrivilegesJSONObjectImpl($this->userPrivileges), $o);
    }


}
