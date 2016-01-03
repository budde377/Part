<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/14
 * Time: 10:12 PM
 */
namespace ChristianBudde\Part\model\page;

use ChristianBudde\Part\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\util\CustomDatabaseTestCase;
use ChristianBudde\Part\util\db\DB;
use ChristianBudde\Part\util\db\StubDBImpl;

class PageContentLibraryImplTest extends CustomDatabaseTestCase
{

    /** @var  DB */
    private $db;
    /** @var  \ChristianBudde\Part\model\page\Page */
    private $existingPage;
    /** @var  Page */
    private $nonExistingPage;
    /** @var  \ChristianBudde\Part\model\page\PageContentLibraryImpl */
    private $existingContentLibrary;
    /** @var  \ChristianBudde\Part\model\page\PageContentLibraryImpl */
    private $nonExistingContentLibrary;

    function __construct()
    {
        parent::__construct($GLOBALS['MYSQL_XML_DIR'] . '/PageContentImplTest.xml');

    }

    public function setUp()
    {
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $container = new StubBackendSingletonContainerImpl();
        $container->setDBInstance($this->db);
        $pageOrder = new PageOrderImpl($container);
        $this->existingPage = $pageOrder->getPage('testpage');
        $this->nonExistingPage = $pageOrder->createPage('nonExisting');
        $this->nonExistingPage->delete();
        $this->existingContentLibrary = new PageContentLibraryImpl($container, $this->existingPage);
        $this->nonExistingContentLibrary = new PageContentLibraryImpl($container, $this->nonExistingPage);
    }

    public function testListPageContentLibraryWillList()
    {
        $this->assertTrue(is_array($list = $this->existingContentLibrary->listContents()));
        $this->assertEquals(2, count($list));
        $this->assertArrayHasKey("", $list);
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\Content", $list[""]);
        $this->assertArrayHasKey("Test", $list);
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\Content", $list["Test"]);
    }


    public function testListPageContentWillReuseInstances()
    {
        $l1 = $this->existingContentLibrary->listContents();
        $l2 = $this->existingContentLibrary->listContents();
        $this->assertTrue($l1[""] === $l2[""]);
        $this->assertTrue($l1["Test"] === $l2["Test"]);
    }

    public function testGetInstanceWillReuseInstance()
    {
        $l1 = $this->existingContentLibrary->listContents();
        $this->assertTrue($l1[""] === $this->existingContentLibrary->getContent());
        $this->assertTrue($l1["Test"] === $this->existingContentLibrary->getContent("Test"));

    }


    public function testGetInstanceWillCreateNewInstance()
    {
        $c = $this->existingContentLibrary->getContent($id = "id" . time());
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\Content", $c);
        $c2 = $this->existingContentLibrary->getContent($id);
        $this->assertTrue($c === $c2);
    }

    public function testGetNewContentWillBeInList()
    {
        $c = $this->existingContentLibrary->getContent($id = "id" . time());
        $list = $this->existingContentLibrary->listContents();
        $this->assertTrue($c === $list[$id]);
        $this->assertArrayHasKey($id, $list);
    }

    public function testGetInstanceFromNonExistingPageIsTheSame()
    {
        $c1 = $this->nonExistingPage->getContent($id1 = "id" . time());
        $c2 = $this->nonExistingPage->getContent($id1);
        $this->assertTrue($c1 === $c2);

    }

    public function testTimestampToListWillFilterList()
    {
        $this->assertEquals(0, count($this->existingContentLibrary->listContents(time())));
    }

    public function testTimestampWillUpdate()
    {
        $this->existingContentLibrary->getContent("Test")->addContent("Some Content");
        $this->assertEquals(1, count($this->existingContentLibrary->listContents(time() - 100)));
    }


    public function testSearchLibraryReturnsArray()
    {

        $this->assertTrue(is_array($this->nonExistingContentLibrary->searchLibrary("some string")));
        $this->assertTrue(is_array($this->existingContentLibrary->searchLibrary("some string")));
    }

    public function testSearchLibraryReturnsAnyOnEmptyString()
    {
        $ar = $this->existingContentLibrary->searchLibrary("");
        $this->assertEquals(2, count($ar));
        $this->assertArrayHasKey("", $ar);
        $this->assertArrayHasKey("Test", $ar);
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\Content", $ar[""]);
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\Content", $ar["Test"]);
    }

    public function testSearchLibraryWillReuseInstances()
    {
        $ar = $this->existingContentLibrary->searchLibrary("");
        $ar2 = $this->existingContentLibrary->searchLibrary("");
        $this->assertTrue($ar[""] === $ar2[""]);
        $this->assertTrue($ar["Test"] === $ar2["Test"]);
    }

    public function testSearchLibraryWillSearchString()
    {
        $ar = $this->existingContentLibrary->searchLibrary("1");
        $this->assertEquals(1, count($ar));
        $this->assertArrayHasKey("Test", $ar);
        $this->assertTrue($ar["Test"] === $this->existingContentLibrary->getContent("Test"));
    }

    public function testSearchLibraryWillLimitSearchToTimestamp()
    {
        $ar = $this->existingContentLibrary->searchLibrary("", time());
        $this->assertEquals(0, count($ar));
    }

    public function testSearchLibraryWillLimitSearchToLaterTimestamp()
    {
        $ar = $this->existingContentLibrary->searchLibrary("", 1110625218);
        $this->assertEquals(1, count($ar));
    }

    public function testLibraryReturnsRightPageInstance()
    {
        $this->assertTrue($this->existingPage === $this->existingContentLibrary->getPage());
    }

    public function testReturnsRightInstance()
    {
        $this->assertTrue($this->existingContentLibrary=== $this->existingContentLibrary->generateTypeHandler());
    }

    public function testChangePageIdOk(){
        $this->existingPage->setID("some_id");
        $ar = $this->existingContentLibrary->searchLibrary("1");
        $this->assertEquals(1, count($ar));
        $this->assertArrayHasKey("Test", $ar);
        $this->assertTrue($ar["Test"] === $this->existingContentLibrary->getContent("Test"));
    }

}