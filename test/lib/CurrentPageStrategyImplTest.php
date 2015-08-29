<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/20/12
 * Time: 1:01 PM
 */
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\model\page\CurrentPageStrategyImpl;
use ChristianBudde\Part\model\page\DefaultPageImpl;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubDefaultPageLibraryImpl;
use ChristianBudde\Part\test\stub\StubPageImpl;
use ChristianBudde\Part\test\stub\StubPageOrderImpl;
use PHPUnit_Framework_TestCase;

class CurrentPageStrategyImplTest extends PHPUnit_Framework_TestCase
{
    /** @var $pageOrder \ChristianBudde\Part\test\stub\StubPageOrderImpl */
    private $pageOrder;
    /** @var \ChristianBudde\Part\model\page\DefaultPageLibrary */
    private $defaultPageLibrary;
    /** @var array */
    private $defaultPageArray;
    private $container;
    /** @var  CurrentPageStrategyImpl */
    private $strategy;

    protected function setUp()
    {
        $this->container = new StubBackendSingletonContainerImpl();
        $this->defaultPageArray['p1'] = new DefaultPageImpl($this->container, 'p1', 'page1', 'template');
        $this->defaultPageArray['p2'] = new DefaultPageImpl($this->container, 'p2', 'page2', 'template2');
        $this->defaultPageLibrary = new StubDefaultPageLibraryImpl($this->defaultPageArray);
        $this->container->setDefaultPageLibraryInstance($this->defaultPageLibrary);
        $this->pageOrder = new StubPageOrderImpl();
        $this->container->setPageOrderInstance($this->pageOrder);
        $this->strategy = New CurrentPageStrategyImpl($this->container);

    }


    public function testCurrentPageWillReturnInstanceOfPageWithNoPagesIndPageOrder()
    {

        $page = $this->strategy->getCurrentPage();
        $this->assertTrue(is_object($page), 'Did not return an object');
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $page, 'Page was not an instance of Page');

    }

    public function testCurrentPagePathWillReturnArrayWithOnePage()
    {


        $path = $this->strategy->getCurrentPagePath();
        $this->assertTrue(is_array($path), 'Did not return an array');
        $this->assertArrayHasKey(0, $path, 'Did not have index 0');
        $this->assertInstanceOf('ChristianBudde\Part\model\page\Page', $path[0]);
    }


    public function testCurrentPageWillReturnFirstElementInNonEmptyOrderWithNoPathInGet()
    {
        $page1 = new StubPageImpl();
        $page2 = new StubPageImpl();

        $order = array();
        $order[null][0] = $page1;
        $order[null][1] = $page2;

        unset($_GET['page']);

        $this->pageOrder->setOrder($order);


        $currentPage = $this->strategy->getCurrentPage();

        $this->assertTrue($page1 === $currentPage, 'Did not return right page');

    }

    public function testCurrentPagePathWillReturnFirstElementInArrayFromNonEmptyOrderWithNoPathInGet()
    {
        $page1 = new StubPageImpl();
        $page2 = new StubPageImpl();

        $order = array();
        $order[null][0] = $page1;
        $order[null][1] = $page2;

        unset($_GET['page']);

        $this->pageOrder->setOrder($order);


        $currentPagePath = $this->strategy->getCurrentPagePath();
        $this->assertTrue(is_array($currentPagePath), 'Did not return an array');
        $this->assertArrayHasKey(0, $currentPagePath, 'Did not have index 0');
        $this->assertTrue($page1 === $currentPagePath[0], "Did not return array of right format");

    }

    public function testCurrentPageWillReturnPageThatMatchWithNonEmptyPathInGet()
    {
        $_GET['page'] = 'page2';

        $page1 = new StubPageImpl();
        $page1->setID('page1');

        $page2 = new StubPageImpl();
        $page2->setID('page2');


        $page3 = new StubPageImpl();
        $page3->setID('page2');


        $subPage1 = new StubPageImpl();
        $subPage1->setID('page2');

        $order = array();
        $order[null][0] = $page1;
        $order[null][1] = $page2;
        $order[null][2] = $page3;

        $order[$page2->getID()][0] = $subPage1;


        $this->pageOrder->setOrder($order);


        $currentPage = $this->strategy->getCurrentPage();

        $this->assertTrue($page2 === $currentPage, 'Did not return right page');
    }


    public function testCurrentPagePathWillReturnFirstElementThatMatchWithNonEmptyPathInGet()
    {
        $_GET['page'] = 'page2';

        $page1 = new StubPageImpl();
        $page1->setID('page1');

        $page2 = new StubPageImpl();
        $page2->setID('page2');

        $page3 = new StubPageImpl();
        $page3->setID('page2');

        $subPage1 = new StubPageImpl();
        $subPage1->setID('page2');


        $order = array();
        $order[null][0] = $page1;
        $order[null][1] = $page2;
        $order[null][2] = $page3;

        $order[$page2->getID()][0] = $subPage1;


        $this->pageOrder->setOrder($order);


        $currentPagePath = $this->strategy->getCurrentPagePath();
        $this->assertTrue(is_array($currentPagePath), 'Did not return an array');
        $this->assertArrayHasKey(0, $currentPagePath, 'Did not have index 0');
        $this->assertTrue($page2 === $currentPagePath[0], "Did not return array of right format");
    }


    public function testCurrentPageWillReturnFirstSubElementThatMatchWithParentMatchAndNonEmptyPathGet()
    {
        $_GET['page'] = 'page2/subPage2';

        $page1 = new StubPageImpl();
        $page1->setID('page1');

        $page2 = new StubPageImpl();
        $page2->setID('page2');

        $subPage1 = new StubPageImpl();
        $subPage1->setID('subPage1');

        $subPage2 = new StubPageImpl();
        $subPage2->setID('subPage2');

        $subPage3 = new StubPageImpl();
        $subPage3->setID('subPage2');


        $order = array();
        $order[null][0] = $page1;
        $order[null][1] = $page2;

        $order[$page2->getID()][0] = $subPage1;
        $order[$page2->getID()][1] = $subPage2;
        $order[$page2->getID()][2] = $subPage3;

        $this->pageOrder->setOrder($order);


        $currentPage = $this->strategy->getCurrentPage();

        $this->assertTrue($subPage2 === $currentPage, 'Did not return right page');


    }

    public function testCurrentPagePathWillReturnArrayWithMatchTopAndSubElementForValidPathInGet()
    {
        $_GET['page'] = 'page2/subPage2';

        $page1 = new StubPageImpl();
        $page1->setID('page1');

        $page2 = new StubPageImpl();
        $page2->setID('page2');

        $subPage1 = new StubPageImpl();
        $subPage1->setID('subPage1');

        $subPage2 = new StubPageImpl();
        $subPage2->setID('subPage2');

        $subPage3 = new StubPageImpl();
        $subPage3->setID('subPage2');


        $order = array();
        $order[null][0] = $page1;
        $order[null][1] = $page2;

        $order[$page2->getID()][0] = $subPage1;
        $order[$page2->getID()][1] = $subPage2;
        $order[$page2->getID()][2] = $subPage3;

        $this->pageOrder->setOrder($order);


        $currentPagePath = $this->strategy->getCurrentPagePath();
        $this->assertTrue(is_array($currentPagePath), 'Did not return an array');
        $this->assertArrayHasKey(0, $currentPagePath, 'Did not have index 0');
        $this->assertArrayHasKey(1, $currentPagePath, 'Did not have index 0');
        $this->assertTrue($page2 === $currentPagePath[0], "Did not return array of right format");
        $this->assertTrue($subPage2 === $currentPagePath[1], "Did not return array of right format");


    }


    public function testCurrentPagePathWillReturnArrayOnePageOnSubLinkNotValid()
    {
        $_GET['page'] = 'page2/subPageX';

        $page1 = new StubPageImpl();
        $page1->setID('page1');

        $page2 = new StubPageImpl();
        $page2->setID('page2');

        $subPage1 = new StubPageImpl();
        $subPage1->setID('subPage1');

        $subPage2 = new StubPageImpl();
        $subPage2->setID('subPage2');

        $subPage3 = new StubPageImpl();
        $subPage3->setID('subPage2');


        $order = array();
        $order[null][0] = $page1;
        $order[null][1] = $page2;

        $order[$page2->getID()][0] = $subPage1;
        $order[$page2->getID()][1] = $subPage2;
        $order[$page2->getID()][2] = $subPage3;

        $this->pageOrder->setOrder($order);


        $currentPagePath = $this->strategy->getCurrentPagePath();
        $this->assertTrue(is_array($currentPagePath), 'Did not return an array');
        $this->assertArrayHasKey(0, $currentPagePath, 'Did not have index 0');
        $this->assertArrayNotHasKey(1, $currentPagePath, 'Did not have index 0');
        $this->assertTrue($page2 !== $currentPagePath[0], "Did not return array of right format");
        $this->assertInstanceOf('ChristianBudde\Part\model\page\NotFoundPageImpl', $currentPagePath[0]);
        //$this->assertEquals(ErrorPage::Error404, $currentPagePath[0]->getError(), 'Not right error code');


    }

    public function testCurrentPageWillReturnOnePageNotGivenOnSubLinkNotValid()
    {
        $_GET['page'] = 'page2/subPageX';

        $page1 = new StubPageImpl();
        $page1->setID('page1');

        $page2 = new StubPageImpl();
        $page2->setID('page2');

        $subPage1 = new StubPageImpl();
        $subPage1->setID('subPage1');

        $subPage2 = new StubPageImpl();
        $subPage2->setID('subPage2');

        $subPage3 = new StubPageImpl();
        $subPage3->setID('subPage2');


        $order = array();
        $order[null][0] = $page1;
        $order[null][1] = $page2;

        $order[$page2->getID()][0] = $subPage1;
        $order[$page2->getID()][1] = $subPage2;
        $order[$page2->getID()][2] = $subPage3;

        $this->pageOrder->setOrder($order);


        $currentPage = $this->strategy->getCurrentPage();

        $this->assertFalse($page2 === $currentPage, 'Did not return right page');
        $this->assertFalse($page1 === $currentPage, 'Did not return right page');
        $this->assertInstanceOf('ChristianBudde\Part\model\page\NotFoundPageImpl', $currentPage);
        // $this->assertEquals(ErrorPage::Error404, $currentPage->getError(), 'Not right error code');


    }

    public function testCurrentPageWillIgnoreEmptyPathsInGet()
    {
        $_GET['page'] = '/////page1////subPage1////';

        $page1 = new StubPageImpl();
        $page1->setID('page1');

        $subPage1 = new StubPageImpl();
        $subPage1->setID('subPage1');


        $order = array();
        $order[null][0] = $page1;
        $order[$page1->getID()][0] = $subPage1;

        $this->pageOrder->setOrder($order);

        $currentPage = $this->strategy->getCurrentPage();

        $this->assertTrue($subPage1 === $currentPage, 'Did not return right page');
    }

    public function testCurrentPagePathWillIgnoreEmptyPathsInGet()
    {
        $_GET['page'] = '/////page1////subPage1////';

        $page1 = new StubPageImpl();
        $page1->setID('page1');

        $subPage1 = new StubPageImpl();
        $subPage1->setID('subPage1');


        $order = array();
        $order[null][0] = $page1;

        $order[$page1->getID()][0] = $subPage1;

        $this->pageOrder->setOrder($order);

        $currentPagePath = $this->strategy->getCurrentPagePath();
        $this->assertTrue(is_array($currentPagePath), 'Did not return an array');
        $this->assertArrayHasKey(0, $currentPagePath, 'Did not have index 0');
        $this->assertArrayHasKey(1, $currentPagePath, 'Did not have index 0');
        $this->assertTrue($page1 === $currentPagePath[0], "Did not return array of right format");
        $this->assertTrue($subPage1 === $currentPagePath[1], "Did not return array of right format");


    }

    public function testCurrentPageStrategyWillLookInInactiveIfNotFoundInActive()
    {
        $_GET['page'] = 'page2';

        $page1 = new StubPageImpl();
        $page1->setID('page1');

        $page2 = new StubPageImpl();
        $page2->setID('page2');


        $order = array();
        $order[null][0] = $page1;

        $this->pageOrder->setOrder($order);
        $this->pageOrder->setInactiveList(array($page2));

        $currentPagePath = $this->strategy->getCurrentPagePath();
        $this->assertTrue(is_array($currentPagePath), 'Did not return an array');
        $this->assertArrayHasKey(0, $currentPagePath, 'Did not have index 0');
        $this->assertTrue($page2 === $currentPagePath[0], "Did not return array of right format");


    }

    public function testCurrentPageStrategyWillLookInDefaultPagesIfNotFoundInActiveOrInactive()
    {
        $_GET['page'] = 'p2';

        $page1 = new StubPageImpl();
        $page1->setID('page1');

        $page2 = new StubPageImpl();
        $page2->setID('page2');


        $order = array();
        $order[null][0] = $page1;

        $this->pageOrder->setOrder($order);
        $this->pageOrder->setInactiveList(array($page2));


        $currentPagePath = $this->strategy->getCurrentPagePath();
        $this->assertTrue(is_array($currentPagePath), 'Did not return an array');
        $this->assertArrayHasKey(0, $currentPagePath, 'Did not have index 0');
        $this->assertTrue($this->defaultPageArray["p2"] === $currentPagePath[0], "Did not return array of right format");


    }


}
