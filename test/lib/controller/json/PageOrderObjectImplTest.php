<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 12:47 PM
 */
namespace ChristianBudde\Part\controller\json;

use ChristianBudde\Part\model\page\StubPageImpl;
use ChristianBudde\Part\model\page\StubPageOrderImpl;
use PHPUnit_Framework_TestCase;

class PageOrderObjectImplTest extends PHPUnit_Framework_TestCase
{

    public function testConstructorWillSetVariables()
    {

        $pageOrder = new StubPageOrderImpl();

        $page1 = new StubPageImpl();
        $page2 = new StubPageImpl();
        $page3 = new StubPageImpl();
        $page4 = new StubPageImpl();

        $page1->setTitle("title1");
        $page2->setTitle("title2");
        $page3->setTitle("title3");
        $page4->setTitle("title4");

        $page1->setID("id1");
        $page2->setID("id2");
        $page3->setID("id3");
        $page4->setID("id4");

        $pageOrder->setInactiveList([$page4]);
        $pageOrder->setOrder([null => [$page1, $page2], "id1" => [$page3]]);


        $object = new PageOrderObjectImpl($pageOrder);

        $this->assertEquals([null => [$page1, $page2], "id1" => [$page3]], $object->getVariable('order'));
        $this->assertEquals([$page4], $object->getVariable('inactive'));
        $this->assertEquals('page_order', $object->getName());

    }


}