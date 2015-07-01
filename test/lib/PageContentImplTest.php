<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 8/13/13
 * Time: 11:41 AM
 * To change this template use File | Settings | File Templates.
 */

namespace ChristianBudde\Part\test;

use ChristianBudde\Part\controller\json\PageContentObjectImpl;
use ChristianBudde\Part\model\page\Page;
use ChristianBudde\Part\model\page\PageContentImpl;
use ChristianBudde\Part\model\page\PageOrderImpl;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubDBImpl;
use ChristianBudde\Part\test\util\CustomDatabaseTestCase;
use ChristianBudde\Part\util\db\DB;

class PageContentImplTest extends CustomDatabaseTestCase
{

    /** @var  DB */
    private $db;
    /** @var  PageContentImpl */
    private $existingContent;
    /** @var  PageContentImpl */
    private $existingContent2;

    /** @var  \ChristianBudde\Part\model\page\PageContentImpl */
    private $nonExistingContent;
    /** @var  \ChristianBudde\Part\model\page\Page */
    private $existingPage;
    /** @var  Page */
    private $nonExistingPage;
    private $existingId2;
    private $container;
    /** @var  PageOrderImpl */
    private $pageOrder;

    function __construct()
    {
        parent::__construct(dirname(__FILE__) . '/../mysqlXML/PageContentImplTest.xml');
    }


    public function setUp()
    {
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->container = new StubBackendSingletonContainerImpl();
        $this->container->setDBInstance($this->db);
        $this->pageOrder = new PageOrderImpl($this->container);
        $this->existingPage = $this->pageOrder->getPage('testpage');
        $this->existingContent = new PageContentImpl($this->container, $this->existingPage);

        $this->existingContent2 = new PageContentImpl($this->container, $this->existingPage, $this->existingId2 = "Test");

        $this->nonExistingPage = $this->pageOrder->createPage('nonExisting');
        $this->nonExistingPage->delete();
        $this->nonExistingContent = new PageContentImpl($this->container, $this->nonExistingPage);
    }

    public function testListContentWillReturnArray()
    {
        $this->assertTrue(is_array($this->existingContent->listContentHistory()));
    }

    public function testListContentOfNonExistingWillReturnArray()
    {
        $this->assertTrue(is_array($this->nonExistingContent->listContentHistory()));
    }

    public function testListContentWillReturnArrayOfRightSize()
    {

        $this->assertEquals(1, count($ar = $this->existingContent->listContentHistory()));
        $this->assertEquals(2, count($ar[0]));
        $this->assertEquals("Some Content", trim($ar[0]['content']));
        $this->assertGreaterThan(0, trim($ar[0]['time']));
    }

    public function testFromWillLimitList()
    {
        $this->assertEquals(0, count($this->existingContent->listContentHistory(time())));
    }

    public function testToWillLimitList()
    {
        $this->existingContent->addContent("TEST!");
        $this->assertEquals(1, count($this->existingContent->listContentHistory(null, time() - 100)));

    }

    public function testFromToWillBeAccurate()
    {
        $this->assertEquals(2, count($this->existingContent2->listContentHistory()));
        $this->existingContent2->addContent("3");
        $history = $this->existingContent2->listContentHistory(null, null, true);
        $this->assertEquals(3, count($history));
        $this->assertEquals([$history[0]], $this->existingContent2->listContentHistory($history[0], $history[0], true));
    }

    public function testOnlyTimestampWillListTimestamps()
    {

        $list1 = $this->existingContent2->listContentHistory(null, null);
        $list2 = $this->existingContent2->listContentHistory(null, null, true);
        $this->assertEquals([$list1[0]['time'], $list1[1]['time']], $list2);
    }


    public function testAddContentWillAddContent()
    {
        $content = "Lorem Ipsum";
        $this->assertGreaterThan(time() - 100, $this->existingContent->addContent($content));
        $ec = $this->existingContent->listContentHistory(time() - 100);
        $this->assertEquals(2, count($this->existingContent->listContentHistory()));
        $this->assertEquals(1, count($ec));
        $this->assertEquals($content, $ec[0]['content']);
    }

    public function testAddContentIsVolatile()
    {
        $this->assertEquals(1, count($this->existingContent->listContentHistory()));
        $this->existingContent->addContent("ASD");
        $this->assertEquals(2, count($this->existingContent->listContentHistory()));
        $this->existingContent = new PageContentImpl($this->container, $this->existingPage);
        $this->assertEquals(2, count($this->existingContent->listContentHistory()));
    }

    public function testCantAddContentToNonExistingPage()
    {
        $this->nonExistingContent->addContent("lol");
        $this->assertEquals(0, count($this->nonExistingContent->listContentHistory()));
    }



    public function testLatestContentWillReturnLatestContent()
    {
        $content = "LoremIp";
        $this->existingContent->addContent($content);
        $this->assertEquals($content, $this->existingContent->latestContent());
    }

    public function testGetContentBeforeTimeReturnsNull()
    {
        $this->assertNull($this->existingContent2->getContentAt(1));
    }

    public function testGetContentNowReturnsRightResult()
    {
        $this->assertEquals($this->existingContent2->latestContent(), $this->existingContent2->getContentAt(time())['content']);
    }

    public function testGetContentBetweenTimesReturnRightResult()
    {
        $this->assertEquals("1", $this->existingContent2->getContentAt(1356994000)['content']);
    }

    public function testLatestContentWillReturnNullOnNoContent()
    {
        $this->assertNull($this->nonExistingContent->latestContent());
    }


    public function testLatestTimeWillReturnNullOnNoContent()
    {
        $this->assertNull($this->nonExistingContent->latestTime());
    }


    public function testLatestTimeWillReturnLatestTime()
    {
        $this->existingContent->addContent("ASD");
        $this->assertGreaterThan(time() - 100, $this->existingContent->latestTime());
    }


    public function testChangePageContentWillUpdatePage()
    {
        $lastTime = $this->existingPage->lastModified();
        $this->existingContent->addContent("HELLO");
        $this->assertGreaterThan($lastTime, $this->existingPage->lastModified());
    }

    public function testContainsSubStringWillReturnFalseIfDoesNotContainContent()
    {
        $this->assertFalse($this->nonExistingContent->containsSubString("non existing substring"));
        $this->assertFalse($this->existingContent->containsSubString("non existing substring"));
        $this->assertFalse($this->existingContent2->containsSubString("non existing substring"));
    }


    public function testContainsSubStringWillReturnFalseIfDoesNotContainContentButOtherDoes()
    {
        $this->assertFalse($this->existingContent2->containsSubString("Some Content"));
    }

    public function testContainsSubStringWillReturnTrueIfContainsString()
    {
        $this->existingContent->addContent($s = "New String");

        $this->assertTrue($this->existingContent->containsSubString("Some"));
        $this->assertTrue($this->existingContent->containsSubString("Content"));
        $this->assertTrue($this->existingContent->containsSubString("Some Content"));
        $this->assertTrue($this->existingContent->containsSubString($s));
    }

    public function testContainsWillRespectTime()
    {
        $this->assertFalse($this->existingContent->containsSubString("Some", time()));
    }

    public function testReturnsRightInstanceOfPage()
    {
        $this->assertTrue($this->existingPage === $this->existingContent->getPage());
    }

    public function testReturnsRightId()
    {
        $this->assertEquals($this->existingId2, $this->existingContent2->getId());
        $this->assertEquals("", $this->existingContent->getId());
    }


    public function testReturnsRightContent()
    {
        $this->assertEquals(new PageContentObjectImpl($this->existingContent2), $this->existingContent2->jsonObjectSerialize());

    }

    public function testLibraryReturnsRightPageInstance()
    {
        $this->assertTrue($this->existingContent === $this->existingContent->generateTypeHandler());
    }

    public function testChangePageIdOk(){
        $this->existingPage->setID("some_id");
        $this->existingContent->addContent("Test");
        $this->assertEquals("Test", $this->existingContent->latestContent());
    }


}