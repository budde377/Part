<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:59 PM
 */
namespace ChristianBudde\Part\controller\json;
use ChristianBudde\Part\model\page\StubPageContentImpl;
use PHPUnit_Framework_TestCase;

class PageContentObjectImplTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorChangesName()
    {
        $content = new StubPageContentImpl();
        $object = new PageContentObjectImpl($content);
        $this->assertEquals('page_content', $object->getName());
    }
}