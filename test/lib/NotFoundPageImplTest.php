<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 5:32 PM
 */

namespace ChristianBudde\Part\test;


use ChristianBudde\Part\controller\json\PageObjectImpl;
use ChristianBudde\Part\model\page\NotFoundPageImpl;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;

class NotFoundPageImplTest extends \PHPUnit_Framework_TestCase{
    /** @var  NotFoundPageImpl */
    private $not_found_page;


    public function testId()
    {
        $this->assertEquals('_404', $this->not_found_page->getID());
    }

    public function testTitle()
    {
        $this->assertEquals('_404', $this->not_found_page->getTitle());
    }

    public function testTemplate()
    {
        $this->assertEquals('_404', $this->not_found_page->getTemplate());
    }

    public function testAlias()
    {
        $this->assertNull($this->not_found_page->getAlias());
    }


    public function testSetId(){
        $this->assertFalse($this->not_found_page->setID('legalId'));
    }

    public function testSetTitleDoesNothing(){
        $this->not_found_page->setTitle('title');
        $this->assertEquals('_404', $this->not_found_page->getTitle());

    }


    public function testSetTemplate()
    {
        $this->assertFalse($this->not_found_page->setTemplate('template'));
        $this->assertEquals('_404', $this->not_found_page->getTemplate());
    }
    public function testSetAlias()
    {
        $this->assertFalse($this->not_found_page->setAlias('//'));
        $this->assertNull($this->not_found_page->getAlias());
    }


    public function testActionsAreFalse(){
        $this->assertFalse($this->not_found_page->exists());
        $this->assertFalse($this->not_found_page->create());
        $this->assertFalse($this->not_found_page->delete());
        $this->assertFalse($this->not_found_page->isEditable());
        $this->assertFalse($this->not_found_page->isValidId('id'));
        $this->assertFalse($this->not_found_page->isValidAlias('//'));
        $this->assertFalse($this->not_found_page->isHidden());
    }

    public function testHideDoesNothing(){
        $this->not_found_page->hide();
        $this->assertFalse($this->not_found_page->isHidden());
    }

    public function testShowDoesNothing(){
        $this->not_found_page->show();
        $this->assertFalse($this->not_found_page->isHidden());
    }


    public function testGetContentIsNullObject(){
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\NullContentImpl", $this->not_found_page->getContent());
    }


    public function testLastModifiedIsMinusOne(){
        $this->assertEquals(-1, $this->not_found_page->lastModified());
    }


    public function testModifyDoesNotChange(){
        $this->not_found_page->modify();
        $this->assertEquals(-1, $this->not_found_page->lastModified());

    }

    public function testCantMatch(){
        $this->assertFalse($this->not_found_page->match('_404'));
    }



    public function testGetVariables(){
        $this->assertNull($this->not_found_page->getVariables());
        $this->assertNull($this->not_found_page->getContentLibrary());
    }

    public function testJsonObject(){
        $obj = $this->not_found_page->jsonObjectSerialize();
        $this->assertEquals(new PageObjectImpl($this->not_found_page), $obj);
        $this->assertEquals($obj->jsonSerialize(), $this->not_found_page->jsonSerialize());
    }

    public function testTypeHandler()
    {
        $this->assertTrue($this->not_found_page->generateTypeHandler() == $this->not_found_page);
    }


    protected function setUp()
    {
        parent::setUp();

        $this->not_found_page = new NotFoundPageImpl(new StubBackendSingletonContainerImpl());
    }




}