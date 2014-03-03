<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/14
 * Time: 10:12 PM
 */
class SiteContentLibraryImplTest extends CustomDatabaseTestCase
{


    /** @var  DB */
    private $db;
    /** @var  PageContentLibraryImpl */
    private $existingContentLibrary;

    function __construct()
    {
        parent::__construct(dirname(__FILE__) . '/mysqlXML/SiteContentLibraryImplTest.xml');

    }

    public function setUp()
    {
        $site = new StubSiteImpl();
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->existingContentLibrary = new SiteContentLibraryImpl($this->db, $site);
    }

    public function testListPageContentLibraryWillList()
    {
        $this->assertTrue(is_array($list = $this->existingContentLibrary->listContents()));
        $this->assertEquals(2, count($list));
        $this->assertArrayHasKey("", $list);
        $this->assertInstanceOf("Content", $list[""]);
        $this->assertArrayHasKey("Test", $list);
        $this->assertInstanceOf("Content", $list["Test"]);
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
        $this->assertInstanceOf("Content", $c);
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


    public function testTimestampToListWillFilterList()
    {
        $this->assertEquals(0, count($this->existingContentLibrary->listContents(time())));
    }

    public function testTimestampWillUpdate(){
        $this->existingContentLibrary->getContent("Test")->addContent("Some Content");
        $this->assertEquals(1, count($this->existingContentLibrary->listContents(time()-100)));
    }


}