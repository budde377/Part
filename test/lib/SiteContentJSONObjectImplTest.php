<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 6:05 PM
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\json\SiteContentObjectImpl;
use PHPUnit_Framework_TestCase;
use ChristianBudde\cbweb\test\stub\StubSiteContentImpl;

class SiteContentJSONObjectImplTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorChangesName()
    {
        $content = new StubSiteContentImpl();
        $object = new SiteContentObjectImpl($content);
        $this->assertEquals('site_content', $object->getName());
    }
}