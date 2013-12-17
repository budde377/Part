<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 24/01/13
 * Time: 08:54
 * To change this template use File | Settings | File Templates.
 */
class PageJSONObjectTranslatorImplTest extends PHPUnit_Framework_TestCase
{

    /** @var PageJSONObjectTranslatorImpl */
    private $pageTranslator;
    /** @var StubPageImpl */
    private $page;
    /** @var StubPageOrderImpl */
    private $pageOrder;


    public function setUp(){
        $this->page = new StubPageImpl();
        $this->page->setID('someId');
        $this->page->setTitle('someTitle');
        $this->page->setAlias('someAlias');
        $this->page->setTemplate('someTemplate');
        $this->pageOrder = new StubPageOrderImpl();
        $this->pageTranslator = new PageJSONObjectTranslatorImpl($this->pageOrder);

    }


    public function testEncodeNullWillBeFalse(){
        $this->assertFalse($this->pageTranslator->encode(null));
    }

    public function testEncodeNonPageWillBeFalse(){
        $this->assertFalse($this->pageTranslator->encode($this));
    }

    public function testEncodeWillReturnJSONObjectMatchingPageObject(){
        $jsonObject = $this->pageTranslator->encode($this->page);
        $this->assertInstanceOf('PageJSONObjectImpl',$jsonObject);
        $this->assertEquals($jsonObject->getVariable('id'),$this->page->getID());
        $this->assertEquals($jsonObject->getVariable('title'),$this->page->getTitle());
        $this->assertEquals($jsonObject->getVariable('alias'),$this->page->getAlias());
        $this->assertEquals($jsonObject->getVariable('template'),$this->page->getTemplate());
    }

    public function testDecodeWillReturnFalseOnNull(){
        $this->assertFalse($this->pageTranslator->decode(null));
    }

    public function testDecodeWillReturnFalseOnNonPage(){
        $this->assertFalse($this->pageTranslator->decode($this));
    }

    public function testDecodeWillReturnFalseOnNoMatchingPageImPageOrder(){
        $jsonObject = $this->pageTranslator->encode($this->page);
        $this->assertFalse($this->pageTranslator->decode($jsonObject));
    }

    public function testDecodeWillReturnFalseOnMissingVariables(){
        $this->setUpOrder();
        $jsonObject = new JSONObjectImpl('page');
        $jsonObject->setVariable('id','someId');
        $this->assertFalse($this->pageTranslator->decode($jsonObject));
    }
    public function testDecodeWillReturnFalseOnWrongName(){
        $this->setUpOrder();
        $jsonObject = new JSONObjectImpl('notPage');
        $jsonObject->setVariable('id','someId');
        $this->assertFalse($this->pageTranslator->decode($jsonObject));
    }

    public function testDecodeWillReturnPageFromPageLibraryOnSuccess(){
        $this->setUpOrder();
        $jsonObject = $this->pageTranslator->encode($this->page);
        $this->assertTrue($this->page === $this->pageTranslator->decode($jsonObject));
    }

    public function setUpOrder(){
        $order = array();
        $order[null][0] = $this->page;
        $this->pageOrder->setOrder($order);
    }
}
