<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 5:39 PM
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\json\ContentJSONObjectImpl;
use PHPUnit_Framework_TestCase;
use ChristianBudde\cbweb\test\stub\StubContentImpl;

class ContentJSONObjectImplTest extends PHPUnit_Framework_TestCase
{

    public function testConstructorWillSetVariables()
    {


        $userLib = new StubContentImpl();
        $userLib->id = "testid";
        $userLib->addContent($t = "TestString");

        $object = new ContentJSONObjectImpl($userLib);

        $this->assertEquals($t, $object->getVariable('latest_content'));
        $this->greaterThanOrEqual(time(), $object->getVariable('latest_time'));
        $this->greaterThanOrEqual($userLib->id, $object->getVariable('id'));
        $this->assertEquals('content', $object->getName());

    }
}