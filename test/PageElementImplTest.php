<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/28/13
 * Time: 10:26 AM
 */

class PageElementImplTest extends PHPUnit_Framework_TestCase{
    /** @var  PageElement */
    private $pageElement;
    public function setUp(){
        $this->pageElement = new PageElementImpl();
    }

    public function testIfGenerateContentReturnsEmptyString(){
        $this->assertEquals("", $this->pageElement->generateContent());
    }

    public function testHasBeenSetUpWillReturnFalseIfNotSetUp(){
        $this->assertFalse($this->pageElement->hasBeenSetUp());
    }

    public function testHasBeenSetUpWillReturnTrueIfHasBeenSetUp(){
        $this->pageElement->setUpElement();
        $this->assertTrue($this->pageElement->hasBeenSetUp());
    }

    public function testHasBeenSetUpWillBeResetAfterGenerate(){
        $this->pageElement->setUpElement();
        $this->pageElement->generateContent();
        $this->assertFalse($this->pageElement->hasBeenSetUp());
    }

    //TODO Test if setUp will be called if not called before generate

} 