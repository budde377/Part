<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 6:05 PM
 */
namespace ChristianBudde\Part\controller\json;


use ChristianBudde\Part\model\site\StubSiteContentImpl;
use PHPUnit_Framework_TestCase;

class SiteContentJSONObjectImplTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorChangesName()
    {
        $content = new StubSiteContentImpl();
        $object = new SiteContentObjectImpl($content);
        $this->assertEquals('site_content', $object->getName());
    }
}