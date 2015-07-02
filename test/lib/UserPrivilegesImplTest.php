<?php
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\controller\json\UserPrivilegesObjectImpl;
use ChristianBudde\Part\model\page\PageOrderImpl;
use ChristianBudde\Part\model\user\UserImpl;
use ChristianBudde\Part\model\user\UserPrivilegesImpl;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubDBImpl;
use ChristianBudde\Part\test\stub\StubPageImpl;
use ChristianBudde\Part\test\stub\StubPageOrderImpl;
use ChristianBudde\Part\test\util\SerializeCustomDatabaseTestCase;
use ChristianBudde\Part\util\db\DB;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 19/01/13
 * Time: 10:32
 */
class UserPrivilegesImplTest extends SerializeCustomDatabaseTestCase
{

    /** @var \ChristianBudde\Part\model\user\User */
    private $user;
    /** @var UserPrivilegesImpl */
    private $userPrivileges;
    /** @var DB */
    private $db;
    /** @var \ChristianBudde\Part\model\page\Page */
    private $page1;
    /** @var $page2 */
    private $page2;

    /** @var  StubPageOrderImpl */
    private $pageLibrary;
    private $container;

    function __construct($dataset = null)
    {
        parent::__construct(dirname(__FILE__) . '/../mysqlXML/UserPrivilegesImplTest.xml', $this->userPrivileges);
    }


    public function setUp()
    {
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $container = new StubBackendSingletonContainerImpl();
        $container->setDBInstance($this->db);
        $this->container = $container;
        $this->user = new UserImpl($container, 'root');
        $pageOrder = new PageOrderImpl($container);
        $this->page1 = $pageOrder->getPage('page');
        $this->page2 = $pageOrder->getPage('page2');
        $this->userPrivileges = new UserPrivilegesImpl($container, $this->user);

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
        $this->userPrivileges = new UserPrivilegesImpl($this->container, $this->user);
        $this->assertTrue($this->userPrivileges->hasRootPrivileges());
    }

    public function testRemoveRootWillBePersistent()
    {
        $this->userPrivileges->addRootPrivileges();
        $this->userPrivileges->revokeRootPrivileges();
        $this->userPrivileges = new UserPrivilegesImpl($this->container, $this->user);
        $this->assertFalse($this->userPrivileges->hasRootPrivileges());
    }

    public function testAddSitePrivilegesWillBePersistent()
    {
        $this->userPrivileges->addSitePrivileges();
        $this->userPrivileges = new UserPrivilegesImpl($this->container, $this->user);
        $this->assertTrue($this->userPrivileges->hasSitePrivileges());
    }

    public function testRemoveSitePrivilegesWillBePersistent()
    {
        $this->userPrivileges->addSitePrivileges();
        $this->userPrivileges->revokeSitePrivileges();
        $this->userPrivileges = new UserPrivilegesImpl($this->container, $this->user);
        $this->assertFalse($this->userPrivileges->hasSitePrivileges());
    }

    public function testAddPagePrivilegesWillBePersistent()
    {
        $this->userPrivileges->addPagePrivileges($this->page1);
        $this->userPrivileges = new UserPrivilegesImpl($this->container, $this->user);
        $this->assertTrue($this->userPrivileges->hasPagePrivileges($this->page1));
    }


    public function testRemovePagePrivilegesWillBePersistent()
    {
        $this->userPrivileges->addPagePrivileges($this->page1);
        $this->userPrivileges->revokePagePrivileges($this->page1);
        $this->userPrivileges = new UserPrivilegesImpl($this->container, $this->user);
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
        $this->userPrivileges = new UserPrivilegesImpl($this->container, $this->user);
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
        $userPrivileges = new UserPrivilegesImpl($this->container, $this->user);
        $this->assertTrue($userPrivileges->hasRootPrivileges());
    }

    public function testPrivilegesIsJSONObjectSerializable()
    {
        $o = $this->userPrivileges->jsonObjectSerialize();
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\UserPrivilegesObjectImpl', $o);
        $this->assertEquals(new UserPrivilegesObjectImpl($this->userPrivileges), $o);
    }

    public function testGetUserIsUser()
    {
        $this->assertTrue($this->userPrivileges->getUser() === $this->user);
    }

    public function testGenerator()
    {
        $this->assertTrue($this->userPrivileges->generateTypeHandler() === $this->userPrivileges);
    }

}
