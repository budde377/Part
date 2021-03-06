<?php
/**
 * User: budde
 * Date: 24/01/13
 * Time: 09:26
 */
namespace ChristianBudde\Part\controller\json;

use ChristianBudde\Part\model\page\StubPageImpl;
use PHPUnit_Framework_TestCase;


class PageObjectImplTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorWillSetVariables()
    {

        $id = 'someId';
        $title = 'someTitle';
        $template = 'someTemplate';
        $alias = 'someAlias';
        $page = new StubPageImpl();
        $page->setID($id);
        $page->setTitle($title);
        $page->setTemplate($template);
        $page->setAlias($alias);
        $page->hide();

        $jsonObject = new PageObjectImpl($page);

        $this->assertEquals('page', $jsonObject->getName());
        $this->assertEquals($id, $jsonObject->getVariable('id'));
        $this->assertEquals($title, $jsonObject->getVariable('title'));
        $this->assertEquals($template, $jsonObject->getVariable('template'));
        $this->assertEquals($alias, $jsonObject->getVariable('alias'));
        $this->assertTrue($jsonObject->getVariable('hidden'));
        $this->assertFalse($jsonObject->getVariable('editable'));
    }

}
