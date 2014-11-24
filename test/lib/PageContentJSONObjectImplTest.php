<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:59 PM
 */
namespace ChristianBudde\cbweb\test;
use ChristianBudde\cbweb\controller\json\PageContentObjectImpl;
use PHPUnit_Framework_TestCase;
use ChristianBudde\cbweb\test\stub\StubPageContentImpl;

class PageContentJSONObjectImplTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorChangesName()
    {
        $content = new StubPageContentImpl();
        $object = new PageContentObjectImpl($content);
        $this->assertEquals('page_content', $object->getName());
    }
}