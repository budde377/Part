<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/17/12
 * Time: 12:58 PM
 * To change this template use File | Settings | File Templates.
 */
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\controller\json\PageOrderObjectImpl;
use ChristianBudde\Part\model\page\PageImpl;
use ChristianBudde\Part\model\page\PageOrder;
use ChristianBudde\Part\model\page\PageOrderImpl;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubCurrentPageStrategyImpl;
use ChristianBudde\Part\test\stub\StubDBImpl;
use ChristianBudde\Part\test\stub\StubPageImpl;
use ChristianBudde\Part\test\util\CustomDatabaseTestCase;
use ChristianBudde\Part\test\util\TruncateOperation;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Extensions_Database_Operation_Composite;
use PHPUnit_Extensions_Database_Operation_Factory;

class PageOrderImplTest extends CustomDatabaseTestCase
{


    /** @var $db \ChristianBudde\Part\test\stub\StubDBImpl */
    private $db;
    /** @var  PageOrderImpl */
    private $pageOrder;
    /** @var  StubBackendSingletonContainerImpl */
    private $backendContainer;


    function __construct()
    {
        $fn = dirname(__FILE__) . '/../mysqlXML/PageOrderImplTest.xml';
        parent::__construct($fn);
    }


    public function setUp()
    {
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->backendContainer = new StubBackendSingletonContainerImpl();
        $this->backendContainer->setDBInstance($this->db);
        $this->pageOrder = new PageOrderImpl($this->backendContainer);
    }


    public function testCreatePageWillReturnPageOnValidID()
    {

        $validID = 'someID';
        $page = $this->pageOrder->createPage($validID);
        $this->assertTrue(is_object($page), 'Did not return an object');
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $page, 'Did not return instance of Page');
        $this->assertEquals($validID, $page->getID(), 'IDs did not match');
        $this->assertTrue($page->exists(), 'New page did not exists');
    }


    public function testCreateWillReturnFALSEWithInvalidID()
    {

        $id = 'invalidID)=)=';
        $page = $this->pageOrder->createPage($id);
        $this->assertFalse($page, 'Did not return false');

    }

    public function testCreateWillReturnFALSEifIDExists()
    {

        $id = 'page';
        $page = $this->pageOrder->createPage($id);
        $this->assertFalse($page, 'Did not return false');

    }

    public function testListPagesWillListInactivePages()
    {

        $pages = $this->pageOrder->listPages(PageOrder::LIST_INACTIVE);
        $this->assertTrue(is_array($pages), 'Did not return array');
        $this->assertTrue(!$this->isAssoc($pages), 'Array was not numeric');
        $this->assertEquals(1, count($pages), 'Did not return array with right number of entrances');
        /** @var $p1 \ChristianBudde\Part\model\page\Page */
        $p1 = array_pop($pages);
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $p1, 'Did not return instance of Page');

        $id1 = $p1->getID();
        $this->assertEquals('page3', $id1);
    }

    public function testListPagesWillListAllActivePages()
    {

        $pages = $this->pageOrder->listPages(PageOrder::LIST_ACTIVE);
        $this->assertTrue(is_array($pages), 'Did not return array');
        $this->assertTrue(!$this->isAssoc($pages), 'Array was not numeric');
        $this->assertEquals(2, count($pages), 'Did not return array with right number of entrances');
        /** @var $p1 \ChristianBudde\Part\model\page\Page */
        $p1 = array_pop($pages);
        /** @var $p2 \ChristianBudde\Part\model\page\Page */
        $p2 = array_pop($pages);

        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $p1, 'Did not return instance of Page');
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $p2, 'Did not return instance of Page');
        $id1 = $p1->getID();
        $id2 = $p2->getID();
        $this->assertNotEquals($id1, $id2, 'IDs did match');
        $this->assertTrue($id1 == 'page' || $id2 == 'page', 'None was page');
        $this->assertTrue($id1 == 'page2' || $id2 == 'page2', 'None was page2');
    }

    public function testListPagesWillListAllPages()
    {

        $pages = $this->pageOrder->listPages(PageOrder::LIST_ALL);
        $this->assertTrue(is_array($pages), 'Did not return array');
        $this->assertTrue(!$this->isAssoc($pages), 'Array was not numeric');
        $this->assertEquals(3, count($pages), 'Did not return array with right number of entrances');
        /** @var $p1 \ChristianBudde\Part\model\page\Page */
        $p1 = array_pop($pages);
        /** @var $p2 \ChristianBudde\Part\model\page\Page */
        $p2 = array_pop($pages);
        /** @var $p3 \ChristianBudde\Part\model\page\Page */
        $p3 = array_pop($pages);
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $p1, 'Did not return instance of Page');
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $p2, 'Did not return instance of Page');
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $p3, 'Did not return instance of Page');
        $id1 = $p1->getID();
        $id2 = $p2->getID();
        $id3 = $p3->getID();
        $this->assertNotEquals($id1, $id2, 'IDs did match');
        $this->assertNotEquals($id2, $id3, 'IDs did match');
        $this->assertTrue($id1 == 'page' || $id2 == 'page' || $id3 == 'page', 'None was page');
        $this->assertTrue($id1 == 'page2' || $id2 == 'page2' || $id3 == 'page2', 'None was page2');
        $this->assertTrue($id1 == 'page3' || $id2 == 'page3' || $id3 == 'page3', 'None was page3');
    }

    public function testCreateWillAddPageToInactiveList()
    {

        $newPage = $this->pageOrder->createPage('someId');

        $pages = $this->pageOrder->listPages(PageOrder::LIST_INACTIVE);
        $this->assertTrue(array_search($newPage, $pages) !== false, 'Did not add new page to inactive pages');
    }


    public function testIsActiveWillReturnTrueIfPageIsActive()
    {


        $pages = $this->pageOrder->listPages(PageOrder::LIST_ACTIVE);
        $activePage = array_pop($pages);
        $this->assertTrue($this->pageOrder->isActive($activePage), 'Did not return true on active page');
    }

    public function testIsActiveWillReturnFalseIfPageIsNotActive()
    {

        $newPage = $this->pageOrder->createPage('someId');

        $this->assertFalse($this->pageOrder->isActive($newPage), 'Did not return false on inactive page');

    }

    public function testDeactivatePageWillChangeStatusOfActivePage()
    {

        $activePages = $this->pageOrder->listPages(PageOrder::LIST_ACTIVE);
        $page = array_pop($activePages);
        $this->assertTrue($this->pageOrder->isActive($page));
        $this->pageOrder->deactivatePage($page);
        $this->assertFalse($this->pageOrder->isActive($page));
    }

    public function testDeactivatePageWillBePersistent()
    {

        $activePages = $this->pageOrder->listPages(PageOrder::LIST_ACTIVE);
        /** @var $page \ChristianBudde\Part\model\page\Page */
        $page = array_pop($activePages);
        $this->assertTrue($this->pageOrder->isActive($page));
        $this->pageOrder->deactivatePage($page);
        $this->assertFalse($this->pageOrder->isActive($page));

        $newPage = $this->pageOrder->getPage($page->getID());
        $this->assertFalse($this->pageOrder->isActive($newPage));
    }

    public function testDeactivatePageWillNotPerserverSubPageOrder()
    {

        $page = $this->pageOrder->getPage('page');
        $this->assertTrue($this->pageOrder->isActive($page));
        $order = $this->pageOrder->getPageOrder($page);
        $this->assertTrue(is_array($order));
        $this->assertEquals(1, count($order));
        $subPage = array_pop($order);
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $subPage);
        $this->pageOrder->deactivatePage($page);
        $this->assertFalse($this->pageOrder->isActive($subPage));
        $this->pageOrder->setPageOrder($page);
        $this->assertFalse($this->pageOrder->isActive($subPage));
    }


    public function testDeleteWillDeletePageAndReturnTrueOnSuccess()
    {

        $pages = $this->pageOrder->listPages(PageOrder::LIST_ALL);
        /** @var $page \ChristianBudde\Part\model\page\Page */
        $page = array_pop($pages);

        $deleteRet = $this->pageOrder->deletePage($page);

        $this->assertTrue($deleteRet);
        $this->assertFalse($page->exists());

        $pages = $this->pageOrder->listPages(PageOrder::LIST_ALL);
        $this->assertTrue(array_search($page, $pages) === false, 'Page was in list');

    }

    public function testDeleteWillDeletePageAndReturnFalseOnDeletionFailure()
    {

        $pages = $this->pageOrder->listPages(PageOrder::LIST_ALL);
        /** @var $page \ChristianBudde\Part\model\page\Page */
        $page = array_pop($pages);
        $altPage = new PageImpl($this->backendContainer, $this->pageOrder, $page->getID(), $page->getTitle(), $page->getTemplate(), $page->getAlias(), $page->lastModified(), $page->isHidden());
        $altPage->delete();

        $deleteRet = $this->pageOrder->deletePage($page);

        $this->assertFalse($deleteRet);

    }

    public function testDeletePageNotGeneratedFromPageOrderReturnFalse()
    {

        $page = new PageImpl($this->backendContainer, $this->pageOrder, 'page', '', '', '', 0, false);
        $this->assertTrue($page->exists());
        $this->assertFalse($this->pageOrder->deletePage($page));
    }


    public function testDeleteOnPageGeneratedFromPageOrderWillResultInDeletionFromPageOrder()
    {

        $pages = $this->pageOrder->listPages(PageOrder::LIST_ALL);
        /** @var $page \ChristianBudde\Part\model\page\Page */
        $page = array_pop($pages);

        $deleteRet = $page->delete();

        $this->assertTrue($deleteRet);
        $this->assertFalse($page->exists());

        $pages = $this->pageOrder->listPages(PageOrder::LIST_ALL);
        $this->assertTrue(array_search($page, $pages) === false, 'Page was in list');
    }

    public function testDeleteOnPageCreatedFromPageOrderWillResultInDeletionFromPageOrder()
    {

        $page = $this->pageOrder->createPage('someid');

        $deleteRet = $page->delete();

        $this->assertTrue($deleteRet);
        $this->assertFalse($page->exists());

        $pages = $this->pageOrder->listPages(PageOrder::LIST_INACTIVE);
        $this->assertTrue(array_search($page, $pages) === false, 'Page was in list');
    }


    public function testDeletePageWithChangeIDOnPageGeneratedByPageOrder()
    {

        $page = $this->pageOrder->createPage('someid');
        $page->setID('someOtherId');

        $deleteRet = $this->pageOrder->deletePage($page);

        $this->assertTrue($deleteRet);
        $this->assertFalse($page->exists());

        $pages = $this->pageOrder->listPages(PageOrder::LIST_ALL);
        $this->assertTrue(array_search($page, $pages) === false, 'Page was in list');
    }


    public function testDeleteActivePageWithChangeIDOnPageGeneratedByPageOrder()
    {

        $pages = $this->pageOrder->listPages(PageOrder::LIST_ACTIVE);
        /** @var $page \ChristianBudde\Part\model\page\Page */
        $page = array_pop($pages);
        $page->setID('someotherid');

        $deleteRet = $this->pageOrder->deletePage($page);

        $this->assertTrue($deleteRet);
        $this->assertFalse($page->exists());

        $pages = $this->pageOrder->listPages(PageOrder::LIST_ACTIVE);
        $this->assertTrue(array_search($page, $pages) === false, 'Page was in list');
    }


    public function testGetPageOrderWillReturnArrayWithPageOrder()
    {

        $topOrder = $this->pageOrder->getPageOrder();

        $this->assertTrue(is_array($topOrder));
        $this->assertTrue(!$this->isAssoc($topOrder));
        $this->assertEquals(1, count($topOrder));
        /** @var $page \ChristianBudde\Part\model\page\Page */
        $page = array_pop($topOrder);
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $page);
        $this->assertEquals('page', $page->getID());

    }

    public function testGetPageOrderWithParentPageWillReturnArrayWithPageOrder()
    {

        $topOrder = $this->pageOrder->getPageOrder();
        /** @var $parentPage \ChristianBudde\Part\model\page\Page */
        $parentPage = array_pop($topOrder);

        $subOrder = $this->pageOrder->getPageOrder($parentPage);

        $this->assertTrue(is_array($subOrder));
        $this->assertTrue(!$this->isAssoc($subOrder));
        $this->assertEquals(1, count($subOrder));
        /** @var $page \ChristianBudde\Part\model\page\Page */
        $page = array_pop($subOrder);
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $page);
        $this->assertEquals('page2', $page->getID());

    }

    /*    public function testGetPageOrderWillThrowExceptionIfWrongParameter()
        {

            $exceptionWasThrown = false;
            try {
                $this->pageOrder->getPageOrder("Invalid Input");
            } catch (Exception $e) {
                $exceptionWasThrown = true;
                $this->assertInstanceOf('ChristianBudde\Part\MalformedParameterException', $e, 'Wrong type of exception');
                $this->assertEquals(1, $e->getParameterNumber(), 'Wrong param number');
                $this->assertEquals('Page|null', $e->getExpectedType(), 'Wrong expected type');


            }
            $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');
        }
    */

    public function testGetPageOrderChangeOfParentIdWillReturnRightOrder()
    {

        $topOrder = $this->pageOrder->getPageOrder();
        /** @var $parentPage \ChristianBudde\Part\model\page\Page */
        $parentPage = array_pop($topOrder);
        $parentPage->setID('someID');
        $subOrder = $this->pageOrder->getPageOrder($parentPage);
        $this->assertTrue(is_array($subOrder));
        $this->assertEquals(1, count($subOrder));

    }

    public function testSetInactivePageWillActivatePage()
    {


        $page = $this->pageOrder->createPage('somePage');
        $this->assertFalse($this->pageOrder->isActive($page), 'Page was active');
        $setPageRet = $this->pageOrder->setPageOrder($page, 0);
        $this->assertTrue($setPageRet, 'Set Page did not return true');
        $this->assertTrue($this->pageOrder->isActive($page));
        $order = $this->pageOrder->getPageOrder();
        $this->assertTrue($order[0] === $page, 'Order was not set');
    }

    public function testSetAppendInactivePageWillActivatePage()
    {


        $page = $this->pageOrder->createPage('somePage');
        $setPageRet = $this->pageOrder->setPageOrder($page, 5);
        $this->assertTrue($setPageRet, 'Did not return true');
        $order = $this->pageOrder->getPageOrder();
        $this->assertTrue($order[1] === $page, 'Order was not set');

    }


    public function testSetPageLastWillSetPageLast()
    {


        $page = $this->pageOrder->createPage('somePage');
        $page2 = $this->pageOrder->createPage('somePage2');
        $setPageRet = $this->pageOrder->setPageOrder($page, PageOrder::PAGE_ORDER_LAST);
        $this->assertTrue($setPageRet, 'Did not return true');
        $setPageRet = $this->pageOrder->setPageOrder($page2, PageOrder::PAGE_ORDER_LAST);
        $this->assertTrue($setPageRet, 'Did not return true');
        $order = $this->pageOrder->getPageOrder();
        $this->assertTrue($order[1] === $page, 'Order was not set');
        $this->assertTrue($order[2] === $page2, 'Order was not set');
    }


    public function testAppendActivePageWillRemoveFromOriginalPlace()
    {


        $topPageOrder = $this->pageOrder->getPageOrder();
        /** @var $topPage \ChristianBudde\Part\model\page\Page */
        $topPage = $topPageOrder[0];
        $subPageOrder = $this->pageOrder->getPageOrder($topPage);
        /** @var $subPage \ChristianBudde\Part\model\page\Page */
        $subPage = $subPageOrder[0];
        $this->pageOrder->setPageOrder($subPage, 1);

        $newTopPageOrder = $this->pageOrder->getPageOrder();
        $newSubPageOrder = $this->pageOrder->getPageOrder($topPage);

        $this->assertEquals([], $newSubPageOrder, 'SubPageOrder was longer than expected');
        $this->assertEquals([$topPage, $subPage], $newTopPageOrder, 'SubPage was not appended in right place');

    }

    public function testAppendPageOnSubOrder()
    {


        $topPageOrder = $this->pageOrder->getPageOrder();
        /** @var $topPage \ChristianBudde\Part\model\page\Page */
        $topPage = $topPageOrder[0];

        $newPage = $this->pageOrder->createPage('someID');
        $this->pageOrder->setPageOrder($newPage, 4, $topPage);

        $newSubPageOrder = $this->pageOrder->getPageOrder($topPage);

        $this->assertEquals(2, count($newSubPageOrder), 'SubPageOrder was not of expected length');
        $this->assertTrue($newPage === $newSubPageOrder[1], 'Was not inserted correctly');

    }

    public function testSetPageOrderReturnFalseOnPageNotInList()
    {

        $newPage = new PageImpl($this->backendContainer, $this->pageOrder, 'someId', '', '', '', 0, false);
        $setReturn = $this->pageOrder->setPageOrder($newPage, 3);
        $this->assertFalse($setReturn, 'Did not return false');
    }

    public function testSetPageOrderReturnFalseOnParentNotInList()
    {

        $newPage = new PageImpl($this->backendContainer, $this->pageOrder,  'page', '', '', '', 0, false);
        $topOrder = $this->pageOrder->getPageOrder();
        $oldPage = $topOrder[0];
        $setReturn = $this->pageOrder->setPageOrder($oldPage, 3, $newPage);
        $this->assertFalse($setReturn, 'Did not return false');
    }

    public function testSetPageOrderReturnFalseOnLoop()
    {

        $newPage = $this->pageOrder->createPage('subsubPage');
        $topPageOrder = $this->pageOrder->getPageOrder();
        $subPageOrder = $this->pageOrder->getPageOrder($topPageOrder[0]);
        $this->pageOrder->setPageOrder($newPage, 0, $subPageOrder[0]);
        $setRet = $this->pageOrder->setPageOrder($topPageOrder[0], 0, $newPage);
        $this->assertFalse($setRet, 'Did not return false');
        $newTopOrder = $this->pageOrder->getPageOrder();
        $this->assertTrue($topPageOrder[0] === $newTopOrder[0], 'Did change order');
    }

    public function testSetPageOrderChangesArePersistent()
    {

        $newPage = $this->pageOrder->createPage('someID');
        $this->pageOrder->setPageOrder($newPage, 0);

        $topOrder = $this->pageOrder->getPageOrder();

        $newPageOrder = new PageOrderImpl($this->backendContainer);
        $newTopOrder = $newPageOrder->getPageOrder();

        /** @var \ChristianBudde\Part\model\page\Page $o1 */
        $o1 = $topOrder[0];
        /** @var \ChristianBudde\Part\model\page\Page $o2 */
        $o2 = $topOrder[1];

        /** @var \ChristianBudde\Part\model\page\Page $o3 */
        $o3 = $newTopOrder[0];
        /** @var \ChristianBudde\Part\model\page\Page $o4 */
        $o4 = $newTopOrder[1];

        $this->assertEquals($o1->getID(), $o3->getID());
        $this->assertEquals($o2->getID(), $o4->getID());
    }


    public function testGetPageWillReturnNullIfPageNotFound()
    {

        $this->assertNull($this->pageOrder->getPage('NonExistingPage'), 'Did not return null');
    }

    public function testGetPageWillReturnPage()
    {

        $page = $this->pageOrder->getPage('page');
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $page, 'Did not return right instance');
        $this->assertEquals('page', $page->getID(), 'IDs did not match');

    }

    public function testGetPageWillReturnInactivePage()
    {

        $page = $this->pageOrder->getPage('page3');
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $page, 'Did not return right instance');
        $this->assertEquals('page3', $page->getID(), 'IDs did not match');

    }

    public function testGetPagePathWillReturnFalseIfPageNIL()
    {

        $page = new StubPageImpl();
        $page->setID('someID');
        $page->setTitle('someTitle');
        $ret = $this->pageOrder->getPagePath($page);
        $this->assertFalse($ret, 'Did not return false');
    }

    public function testGetPagePathWillReturnEmptyArrayIfPageIsInactive()
    {

        $page = $this->pageOrder->getPage('page3');
        $ret = $this->pageOrder->getPagePath($page);
        $this->assertTrue(is_array($ret), 'Did not return array');
        $this->assertEquals(0, count($ret), 'Did not return empty array');

    }

    public function testGetPagePathWillReturnNumericArrayWithPathOfPages()
    {

        $page = $this->pageOrder->getPage('page2');
        $ret = $this->pageOrder->getPagePath($page);
        $this->assertTrue(is_array($ret), 'Did not return an array');
        $this->assertEquals(2, count($ret), 'Did not return array of right size');
        $this->assertArrayHasKey(0, $ret, 'Array was not numeric');
        /** @var $p \ChristianBudde\Part\model\page\Page */
        $p = $ret[0];
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $p);
        $this->assertEquals('page', $p->getID(), 'IDs did not match');

        $this->assertArrayHasKey(1, $ret, 'Array was not numeric');
        /** @var $p \ChristianBudde\Part\model\page\Page */
        $p = $ret[1];
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $p);
        $this->assertEquals('page2', $p->getID(), 'IDs did not match');

    }

    public function testBug134(){
        $page1 = $this->pageOrder->getPage('page');
        $page2 = $this->pageOrder->getPage('page2');
        $page3 = $this->pageOrder->getPage('page3');
        $page4 = $this->pageOrder->createPage('page4');
        $this->pageOrder->setPageOrder($page2, 1);
        $this->pageOrder->setPageOrder($page3, 2);
        $this->pageOrder->setPageOrder($page4, 3);
        $this->pageOrder->setPageOrder($page1, 3);
        $this->assertEquals([$page2, $page3, $page4, $page1], $this->pageOrder->getPageOrder());
        $pageOrder = new PageOrderImpl($this->backendContainer);
        $page1 = $pageOrder->getPage('page');
        $page2 = $pageOrder->getPage('page2');
        $page3 = $pageOrder->getPage('page3');
        $page4 = $pageOrder->getPage('page4');
        $this->assertEquals([$page2, $page3, $page4, $page1], $pageOrder->getPageOrder());

    }

    public function testBug133(){
        $page1 = $this->pageOrder->getPage('page');
        $page2 = $this->pageOrder->getPage('page2');
        $page3 = $this->pageOrder->getPage('page3');

        $this->pageOrder->setPageOrder($page3, 0, $page2);
        $this->pageOrder->deactivatePage($page1);
        $this->assertFalse($this->pageOrder->isActive($page1));
        $this->assertFalse($this->pageOrder->isActive($page2));
        $this->assertFalse($this->pageOrder->isActive($page3));
    }

    public function testBug135(){
        $page1 = $this->pageOrder->getPage('page');
        $page3 = $this->pageOrder->getPage('page3');
        $this->pageOrder->setPageOrder($page3, 1, $page1);
        $this->pageOrder->setPageOrder($page3, 0, $page1);

    }


    public function testGetCurrentPageReturnsInstanceFromStrategy()
    {
        $strategy = new StubCurrentPageStrategyImpl();
        $strategy->setCurrentPage($p = new StubPageImpl());
        $this->backendContainer->setCurrentPageStrategyInstance($strategy);
        $this->assertTrue($p === $this->pageOrder->getCurrentPage());
    }

    public function testPageOrderReturnsRightJSONObject()
    {
        $this->assertEquals(new PageOrderObjectImpl($this->pageOrder), $this->pageOrder->jsonObjectSerialize());

    }

    public function testGenerateTypeHandlerReusesInstance(){

        $this->assertEquals( $this->pageOrder, $this->pageOrder->generateTypeHandler());
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
        $pdo = self::$pdo;
        return $this->createDefaultDBConnection($pdo);
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/../mysqlXML/PageOrderImplTest.xml');
    }

    /**
     * @param array $array
     * @return bool
     */
    private function isAssoc($array)
    {
        return (array_keys($array) !== range(0, count($array) - 1));
    }


}
