<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:59 PM
 */

class PageContentJSONObjectImplTest extends PHPUnit_Framework_TestCase{
    public function testConstructorChangesName(){
        $content = new StubPageContentImpl();
        $object = new PageContentJSONObjectImpl($content);
        $this->assertEquals('page_content', $object->getName());
    }
} 