<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 6:05 PM
 */
use ChristianBudde\cbweb\SiteContentJSONObjectImpl;

class SiteContentJSONObjectImplTest extends PHPUnit_Framework_TestCase{
    public function testConstructorChangesName(){
        $content = new StubSiteContentImpl();
        $object = new SiteContentJSONObjectImpl($content);
        $this->assertEquals('site_content', $object->getName());
    }
} 