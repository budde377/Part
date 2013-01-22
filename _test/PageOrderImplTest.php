<?php
require_once dirname(__FILE__) . '/../_class/PageOrderImpl.php';
require_once dirname(__FILE__) . '/../_class/PageImpl.php';
require_once dirname(__FILE__) . '/_stub/StubDBImpl.php';
require_once dirname(__FILE__) . '/_stub/StubPageImpl.php';
require_once dirname(__FILE__) . '/TruncateOperation.php';
require_once dirname(__FILE__) . '/MySQLConstants.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/17/12
 * Time: 12:58 PM
 * To change this template use File | Settings | File Templates.
 */
class PageOrderImplTest extends PHPUnit_Extensions_Database_TestCase
{


    /** @var $db StubDBImpl */
    private $db;
    /** @var $pdo PDO */
    private $pdo;

    public function setUp()
    {
        parent::setUp();
        $this->pdo = new PDO('mysql:dbname=' . self::database . ';host=' . self::host, self::username, self::password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $this->db = new StubDBImpl();
        $this->db->setConnection($this->pdo);
    }


    public function testCreatePageWillReturnPageOnValidID()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $validID = 'someID';
        $page = $pageOrder->createPage($validID);
        $this->assertTrue(is_object($page), 'Did not return an object');
        $this->assertInstanceOf('Page', $page, 'Did not return instance of Page');
        $this->assertEquals($validID, $page->getID(), 'IDs did not match');
        $this->assertTrue($page->exists(), 'New page did not exists');
    }


    public function testCreateWillReturnFALSEWithInvalidID()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $id = 'invalidID)=)=';
        $page = $pageOrder->createPage($id);
        $this->assertFalse($page, 'Did not return false');

    }

    public function testCreateWillReturnFALSEifIDExists()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $id = 'page';
        $page = $pageOrder->createPage($id);
        $this->assertFalse($page, 'Did not return false');

    }

    public function testListPagesWillListInactivePages()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $pages = $pageOrder->listPages(PageOrder::LIST_INACTIVE);
        $this->assertTrue(is_array($pages), 'Did not return array');
        $this->assertTrue(!$this->isAssoc($pages), 'Array was not numeric');
        $this->assertEquals(1, count($pages), 'Did not return array with right number of entrances');
        /** @var $p1 Page */
        $p1 = array_pop($pages);
        $this->assertInstanceOf('Page', $p1, 'Did not return instance of Page');

        $id1 = $p1->getID();
        $this->assertEquals('page3', $id1);
    }

    public function testListPagesWillListAllActivePages()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $pages = $pageOrder->listPages(PageOrder::LIST_ACTIVE);
        $this->assertTrue(is_array($pages), 'Did not return array');
        $this->assertTrue(!$this->isAssoc($pages), 'Array was not numeric');
        $this->assertEquals(2, count($pages), 'Did not return array with right number of entrances');
        /** @var $p1 Page */
        $p1 = array_pop($pages);
        /** @var $p2 Page */
        $p2 = array_pop($pages);

        $this->assertInstanceOf('Page', $p1, 'Did not return instance of Page');
        $this->assertInstanceOf('Page', $p2, 'Did not return instance of Page');
        $id1 = $p1->getID();
        $id2 = $p2->getID();
        $this->assertNotEquals($id1, $id2, 'IDs did match');
        $this->assertTrue($id1 == 'page' || $id2 == 'page', 'None was page');
        $this->assertTrue($id1 == 'page2' || $id2 == 'page2', 'None was page2');
    }

    public function testListPagesWillListAllPages()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $pages = $pageOrder->listPages(PageOrder::LIST_ALL);
        $this->assertTrue(is_array($pages), 'Did not return array');
        $this->assertTrue(!$this->isAssoc($pages), 'Array was not numeric');
        $this->assertEquals(3, count($pages), 'Did not return array with right number of entrances');
        /** @var $p1 Page */
        $p1 = array_pop($pages);
        /** @var $p2 Page */
        $p2 = array_pop($pages);
        /** @var $p3 Page */
        $p3 = array_pop($pages);
        $this->assertInstanceOf('Page', $p1, 'Did not return instance of Page');
        $this->assertInstanceOf('Page', $p2, 'Did not return instance of Page');
        $this->assertInstanceOf('Page', $p3, 'Did not return instance of Page');
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
        $pageOrder = new PageOrderImpl($this->db);
        $newPage = $pageOrder->createPage('someId');

        $pages = $pageOrder->listPages(PageOrder::LIST_INACTIVE);
        $this->assertTrue(array_search($newPage, $pages) !== false, 'Did not add new page to inactive pages');
    }


    public function testIsActiveWillReturnTrueIfPageIsActive()
    {
        $pageOrder = new PageOrderImpl($this->db);

        $pages = $pageOrder->listPages(PageOrder::LIST_ACTIVE);
        $activePage = array_pop($pages);
        $this->assertTrue($pageOrder->isActive($activePage), 'Did not return true on active page');
    }

    public function testIsActiveWillReturnFalseIfPageIsNotActive()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $newPage = $pageOrder->createPage('someId');

        $this->assertFalse($pageOrder->isActive($newPage), 'Did not return false on inactive page');

    }

    public function testDeactivatePageWillChangeStatusOfActivePage()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $activePages = $pageOrder->listPages(PageOrder::LIST_ACTIVE);
        $page = array_pop($activePages);
        $this->assertTrue($pageOrder->isActive($page));
        $pageOrder->deactivatePage($page);
        $this->assertFalse($pageOrder->isActive($page));
    }

    public function testDeactivatePageWillBePersistent(){
        $pageOrder = new PageOrderImpl($this->db);
        $activePages = $pageOrder->listPages(PageOrder::LIST_ACTIVE);
        /** @var $page Page */
        $page = array_pop($activePages);
        $this->assertTrue($pageOrder->isActive($page));
        $pageOrder->deactivatePage($page);
        $this->assertFalse($pageOrder->isActive($page));
        $pageOrder = new PageOrderImpl($this->db);
        $newPage = $pageOrder->getPage($page->getID());
        $this->assertFalse($pageOrder->isActive($newPage));
    }


    public function testDeleteWillDeletePageAndReturnTrueOnSuccess()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $pages = $pageOrder->listPages(PageOrder::LIST_ALL);
        /** @var $page Page */
        $page = array_pop($pages);

        $deleteRet = $pageOrder->deletePage($page);

        $this->assertTrue($deleteRet);
        $this->assertFalse($page->exists());

        $pages = $pageOrder->listPages(PageOrder::LIST_ALL);
        $this->assertTrue(array_search($page, $pages) === false, 'Page was in list');

    }

    public function testDeleteWillDeletePageAndReturnFalseOnDeletionFailure()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $pages = $pageOrder->listPages(PageOrder::LIST_ALL);
        /** @var $page Page */
        $page = array_pop($pages);
        $altPage = new PageImpl($page->getID(), $this->db);
        $altPage->delete();

        $deleteRet = $pageOrder->deletePage($page);

        $this->assertFalse($deleteRet);

    }

    public function testDeletePageNotGeneratedFromPageOrderReturnFalse()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $page = new PageImpl('page', $this->db);
        $this->assertTrue($page->exists());
        $this->assertFalse($pageOrder->deletePage($page));
    }


    public function testDeleteOnPageGeneratedFromPageOrderWillResultInDeletionFromPageOrder()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $pages = $pageOrder->listPages(PageOrder::LIST_ALL);
        /** @var $page Page */
        $page = array_pop($pages);

        $deleteRet = $page->delete();

        $this->assertTrue($deleteRet);
        $this->assertFalse($page->exists());

        $pages = $pageOrder->listPages(PageOrder::LIST_ALL);
        $this->assertTrue(array_search($page, $pages) === false, 'Page was in list');
    }

    public function testDeleteOnPageCreatedFromPageOrderWillResultInDeletionFromPageOrder()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $page = $pageOrder->createPage('someid');

        $deleteRet = $page->delete();

        $this->assertTrue($deleteRet);
        $this->assertFalse($page->exists());

        $pages = $pageOrder->listPages(PageOrder::LIST_INACTIVE);
        $this->assertTrue(array_search($page, $pages) === false, 'Page was in list');
    }


    public function testDeletePageWithChangeIDOnPageGeneratedByPageOrder()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $page = $pageOrder->createPage('someid');
        $page->setID('someOtherId');

        $deleteRet = $pageOrder->deletePage($page);

        $this->assertTrue($deleteRet);
        $this->assertFalse($page->exists());

        $pages = $pageOrder->listPages(PageOrder::LIST_ALL);
        $this->assertTrue(array_search($page, $pages) === false, 'Page was in list');
    }


    public function testDeleteActivePageWithChangeIDOnPageGeneratedByPageOrder()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $pages = $pageOrder->listPages(PageOrder::LIST_ACTIVE);
        /** @var $page Page */
        $page = array_pop($pages);
        $page->setID('someotherid');

        $deleteRet = $pageOrder->deletePage($page);

        $this->assertTrue($deleteRet);
        $this->assertFalse($page->exists());

        $pages = $pageOrder->listPages(PageOrder::LIST_ACTIVE);
        $this->assertTrue(array_search($page, $pages) === false, 'Page was in list');
    }


    public function testGetPageOrderWillReturnArrayWithPageOrder()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $topOrder = $pageOrder->getPageOrder();

        $this->assertTrue(is_array($topOrder));
        $this->assertTrue(!$this->isAssoc($topOrder));
        $this->assertEquals(1, count($topOrder));
        /** @var $page Page */
        $page = array_pop($topOrder);
        $this->assertInstanceOf('Page', $page);
        $this->assertEquals('page', $page->getID());

    }


    public function testGetPageOrderWithParentPageWillReturnArrayWithPageOrder()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $topOrder = $pageOrder->getPageOrder();
        /** @var $parentPage Page */
        $parentPage = array_pop($topOrder);

        $subOrder = $pageOrder->getPageOrder($parentPage);

        $this->assertTrue(is_array($subOrder));
        $this->assertTrue(!$this->isAssoc($subOrder));
        $this->assertEquals(1, count($subOrder));
        /** @var $page Page */
        $page = array_pop($subOrder);
        $this->assertInstanceOf('Page', $page);
        $this->assertEquals('page2', $page->getID());

    }

/*    public function testGetPageOrderWillThrowExceptionIfWrongParameter()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $exceptionWasThrown = false;
        try {
            $pageOrder->getPageOrder("Invalid Input");
        } catch (Exception $e) {
            $exceptionWasThrown = true;
            $this->assertInstanceOf('MalformedParameterException', $e, 'Wrong type of exception');
            $this->assertEquals(1, $e->getParameterNumber(), 'Wrong param number');
            $this->assertEquals('Page|null', $e->getExpectedType(), 'Wrong expected type');


        }
        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');
    }
*/

    public function testGetPageOrderChangeOfParentIdWillReturnRightOrder()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $topOrder = $pageOrder->getPageOrder();
        /** @var $parentPage Page */
        $parentPage = array_pop($topOrder);
        $parentPage->setID('someID');
        $subOrder = $pageOrder->getPageOrder($parentPage);
        $this->assertTrue(is_array($subOrder));
        $this->assertEquals(1, count($subOrder));

    }

    public function testSetInactivePageWillActivatePage()
    {

        $pageOrder = new PageOrderImpl($this->db);
        $page = $pageOrder->createPage('somePage');
        $this->assertFalse($pageOrder->isActive($page), 'Page was active');
        $setPageRet = $pageOrder->setPageOrder($page, 0);
        $this->assertTrue($setPageRet, 'Set Page did not return true');
        $this->assertTrue($pageOrder->isActive($page));
        $order = $pageOrder->getPageOrder();
        $this->assertTrue($order[0] === $page, 'Order was not set');
    }

    public function testSetAppendInactivePageWillActivatePage()
    {
        $pageOrder = new PageOrderImpl($this->db);

        $page = $pageOrder->createPage('somePage');
        $setPageRet = $pageOrder->setPageOrder($page, 5);
        $this->assertTrue($setPageRet, 'Did not return true');
        $order = $pageOrder->getPageOrder();
        $this->assertTrue($order[1] === $page, 'Order was not set');

    }


    public function testSetPageLastWillSetPageLast(){

        $pageOrder = new PageOrderImpl($this->db);
        $page = $pageOrder->createPage('somePage');
        $page2 = $pageOrder->createPage('somePage2');
        $setPageRet = $pageOrder->setPageOrder($page,PageOrder::PAGE_ORDER_LAST);
        $this->assertTrue($setPageRet, 'Did not return true');
        $setPageRet = $pageOrder->setPageOrder($page2,PageOrder::PAGE_ORDER_LAST);
        $this->assertTrue($setPageRet, 'Did not return true');
        $order = $pageOrder->getPageOrder();
        $this->assertTrue($order[1] === $page, 'Order was not set');
        $this->assertTrue($order[2] === $page2, 'Order was not set');
    }


    public function testAppendActivePageWillRemoveFromOriginalPlace()
    {
        $pageOrder = new PageOrderImpl($this->db);

        $topPageOrder = $pageOrder->getPageOrder();
        /** @var $topPage Page */
        $topPage = $topPageOrder[0];
        $subPageOrder = $pageOrder->getPageOrder($topPage);
        /** @var $subPage Page */
        $subPage = $subPageOrder[0];
        $pageOrder->setPageOrder($subPage, 1);

        $newTopPageOrder = $pageOrder->getPageOrder();
        $newSubPageOrder = $pageOrder->getPageOrder($topPage);

        $this->assertEquals(0, count($newSubPageOrder), 'SubPageOrder was longer than expected');
        $this->assertTrue($subPage === $newTopPageOrder[0], 'SubPage was not appended in right place');

    }

    public function testAppendPageOnSubOrder()
    {
        $pageOrder = new PageOrderImpl($this->db);

        $topPageOrder = $pageOrder->getPageOrder();
        /** @var $topPage Page */
        $topPage = $topPageOrder[0];

        $newPage = $pageOrder->createPage('someID');
        $pageOrder->setPageOrder($newPage, 4, $topPage);

        $newSubPageOrder = $pageOrder->getPageOrder($topPage);

        $this->assertEquals(2, count($newSubPageOrder), 'SubPageOrder was not of expected length');
        $this->assertTrue($newPage === $newSubPageOrder[1], 'Was not inserted correctly');

    }

    public function testSetPageOrderReturnFalseOnPageNotInList()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $newPage = new PageImpl('someId', $this->db);
        $setReturn = $pageOrder->setPageOrder($newPage, 3);
        $this->assertFalse($setReturn, 'Did not return false');
    }

    public function testSetPageOrderReturnFalseOnParentNotInList()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $newPage = new PageImpl('page', $this->db);
        $topOrder = $pageOrder->getPageOrder();
        $oldPage = $topOrder[0];
        $setReturn = $pageOrder->setPageOrder($oldPage, 3, $newPage);
        $this->assertFalse($setReturn, 'Did not return false');
    }

    /*
    public function testSetPageOrderThrowExceptionOnMalformedParentPageInput()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $exceptionWasThrown = false;
        $topOrder = $pageOrder->getPageOrder();
        $oldPage = $topOrder[0];
        try {
            $pageOrder->setPageOrder($oldPage, 1, "INVALID INPUT");
        } catch (Exception $e) {
            $exceptionWasThrown = true;
            $this->assertInstanceOf('MalformedParameterException', $e, 'Wrong type of exception');
            $this->assertEquals(3, $e->getParameterNumber(), 'Wrong param number');
            $this->assertEquals('Page|null', $e->getExpectedType(), 'Wrong expected type');


        }
        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');
    }

    */
    public function testSetPageOrderReturnFalseOnLoop()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $newPage = $pageOrder->createPage('subsubPage');
        $topPageOrder = $pageOrder->getPageOrder();
        $subPageOrder = $pageOrder->getPageOrder($topPageOrder[0]);
        $pageOrder->setPageOrder($newPage, 0, $subPageOrder[0]);
        $setRet = $pageOrder->setPageOrder($topPageOrder[0], 0, $newPage);
        $this->assertFalse($setRet, 'Did not return false');
        $newTopOrder = $pageOrder->getPageOrder();
        $this->assertTrue($topPageOrder[0] === $newTopOrder[0], 'Did change order');
    }

    public function testSetPageOrderChangesArePersistent()
    {
        $pageOrder = new PageOrderImpl($this->db);
        $newPage = $pageOrder->createPage('someID');
        $pageOrder->setPageOrder($newPage, 0);

        $topOrder = $pageOrder->getPageOrder();

        $newPageOrder = new PageOrderImpl($this->db);
        $newTopOrder = $newPageOrder->getPageOrder();

        $this->assertEquals($topOrder[0]->getID(), $newTopOrder[0]->getID());
        $this->assertEquals($topOrder[1]->getID(), $newTopOrder[1]->getID());
    }


    public function testGetPageWillReturnNullIfPageNotFound(){
        $pageOrder = new PageOrderImpl($this->db);
        $this->assertNull($pageOrder->getPage('NonExistingPage'),'Did not return null');
    }

    public function testGetPageWillReturnPage(){
        $pageOrder = new PageOrderImpl($this->db);
        $page = $pageOrder->getPage('page');
        $this->assertInstanceOf('Page',$page,'Did not return right instance');
        $this->assertEquals('page',$page->getID(),'IDs did not match');

    }
    public function testGetPageWillReturnInactivePage(){
        $pageOrder = new PageOrderImpl($this->db);
        $page = $pageOrder->getPage('page3');
        $this->assertInstanceOf('Page',$page,'Did not return right instance');
        $this->assertEquals('page3',$page->getID(),'IDs did not match');

    }

    public function testGetPagePathWillReturnFalseIfPageNIL(){
        $pageOrder = new PageOrderImpl($this->db);
        $page = new StubPageImpl();
        $page->setID('someID');
        $page->setTitle('someTitle');
        $ret = $pageOrder->getPagePath($page);
        $this->assertFalse($ret,'Did not return false');
    }

    public function testGetPagePathWillReturnEmptyArrayIfPageIsInactive(){
        $pageOrder = new PageOrderImpl($this->db);
        $page = $pageOrder->getPage('page3');
        $ret = $pageOrder->getPagePath($page);
        $this->assertTrue(is_array($ret),'Did not return array');
        $this->assertEquals(0,count($ret),'Did not return empty array');

    }

    public function testGetPagePathWillReturnNumericArrayWithPathOfPages(){
        $pageOrder = new PageOrderImpl($this->db);
        $page = $pageOrder->getPage('page2');
        $ret = $pageOrder->getPagePath($page);
        $this->assertTrue(is_array($ret),'Did not return an array');
        $this->assertEquals(2,count($ret),'Did not return array of right size');
        $this->assertArrayHasKey(0,$ret,'Array was not numeric');
        /** @var $p Page */
        $p = $ret[0];
        $this->assertInstanceOf('Page',$p);
        $this->assertEquals('page',$p->getID(),'IDs did not match');

        $this->assertArrayHasKey(1,$ret,'Array was not numeric');
        /** @var $p Page */
        $p = $ret[1];
        $this->assertInstanceOf('Page',$p);
        $this->assertEquals('page2',$p->getID(),'IDs did not match');

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
        return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/_mysqlXML/PageOrderImplTest.xml');
    }

    /**
     * @param array $array
     * @return bool
     */
    private function isAssoc($array)
    {
        return (array_keys($array) !== range(0, count($array) - 1));
    }


    const database = MySQLConstants::MYSQL_DATABASE;
    const password = MySQLConstants::MYSQL_PASSWORD;
    const username = MySQLConstants::MYSQL_USERNAME;
    const host = MySQLConstants::MYSQL_HOST;

}
