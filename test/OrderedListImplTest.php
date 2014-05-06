<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/5/14
 * Time: 3:20 PM
 */
class OrderedListImplTest extends CustomDatabaseTestCase
{


    /** @var  DB */
    private $db;
    /** @var  OrderedListImpl */
    private $nonEmptyList;
    private $nonEmptyListId = "someList";
    /** @var  OrderedListImpl */
    private $emptyList;
    private $emptyListId = "emptyList";

    function __construct()
    {
        parent::__construct(dirname(__FILE__) . '/mysqlXML/OrderedListImplTest.xml');
    }


    public function setUp()
    {
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->nonEmptyList = new OrderedListImpl($this->db, $this->nonEmptyListId);
        $this->emptyList = new OrderedListImpl($this->db, $this->emptyListId);

    }

    public function testSizeOfListReturnsRightSize()
    {
        $this->assertEquals(2, $this->nonEmptyList->size());
        $this->assertEquals(0, $this->emptyList->size());
    }

    public function testSizeOfListWillIncrease()
    {
        $this->emptyList->createElement();
        $this->assertEquals(1, $this->emptyList->size());
        $this->emptyList->createElement();
        $this->assertEquals(2, $this->emptyList->size());

    }

    public function testCreateElementWillCreateNewElement()
    {
        $elm = $this->nonEmptyList->createElement();
        $elm2 = $this->nonEmptyList->createElement();
        $this->assertInstanceOf("OrderedListElement", $elm);
        $this->assertInstanceOf("OrderedListElement", $elm2);
        $this->assertTrue($elm !== $elm2);
    }

    public function testCreateIsPersistent()
    {
        $this->emptyList->createElement();
        $this->assertEquals(1, $this->emptyList->size());
        $l = new OrderedListImpl($this->db, $this->emptyListId);
        $this->assertEquals(1, $l->size());
    }

    public function testGetElementAtWillReturnElement()
    {
        $this->assertInstanceOf("OrderedListElement", $elm = $this->nonEmptyList->getElementAt(0));
        $this->assertTrue($this->nonEmptyList->isInList($elm));
    }

    public function testGetElementAtWillReturnElementAtRightPos()
    {
        $this->assertInstanceOf("OrderedListElement", $elm = $this->nonEmptyList->getElementAt(1));
        $this->assertTrue($this->nonEmptyList->isInList($elm));
        $this->assertEquals(1, $this->nonEmptyList->getElementOrder($elm));
    }

    public function testGetElementAtWillReturnFirstIfLessThanZero()
    {
        $this->assertEquals(0, $this->nonEmptyList->getElementOrder($this->nonEmptyList->getElementAt(-1)));
    }

    public function testGetElementAtWillReturnLastIfLargerThanMax()
    {
        $this->assertEquals(1, $this->nonEmptyList->getElementOrder($this->nonEmptyList->getElementAt(10)));
    }

    public function testGetElementAtWillReturnNullOnEmptyList()
    {
        $this->assertNull($this->emptyList->getElementAt(0));
    }

    public function testFirstElementWillReturnFirstElement()
    {
        $this->assertTrue($this->nonEmptyList->firstElement() === $this->nonEmptyList->getElementAt(0));
        $this->assertTrue($this->emptyList->firstElement() === $this->emptyList->getElementAt(0));
    }

    public function testLastElementWillReturnLastElement()
    {
        $this->assertTrue($this->nonEmptyList->lastElement() === $this->nonEmptyList->getElementAt(1));
        $this->assertTrue($this->emptyList->lastElement() === $this->emptyList->getElementAt(0));
    }

    public function testGetElementOrderReturnsFALSEOnNotInList()
    {
        $this->assertTrue(FALSE === $this->emptyList->getElementOrder(new StubOrderedListElementImpl()));
    }

    public function testDeleteDeletes()
    {
        $elm = $this->nonEmptyList->firstElement();
        $this->assertTrue($this->nonEmptyList->isInList($elm));
        $this->nonEmptyList->deleteElement($elm);
        $this->assertFalse($this->nonEmptyList->isInList($elm));
    }

    public function testDeleteIsPersistent()
    {
        $elm = $this->nonEmptyList->firstElement();
        $this->assertTrue($this->nonEmptyList->isInList($elm));
        $this->nonEmptyList->deleteElement($elm);
        $l = new OrderedListImpl($this->db, $this->nonEmptyListId);
        $this->assertEquals(1, $l->size());

    }

    public function testCreateAfterDeleteIsOK()
    {
        $elm = $this->nonEmptyList->firstElement();
        $this->nonEmptyList->deleteElement($elm);
        $elm2 = $this->nonEmptyList->createElement();
        $this->assertTrue($this->nonEmptyList->isInList($elm2));
        $this->assertEquals(1, $this->nonEmptyList->getElementOrder($elm2));
    }

    public function testSameInstanceAfterDelete()
    {
        $elm1 = $this->nonEmptyList->getElementAt(0);
        $elm2 = $this->nonEmptyList->getElementAt(1);
        $this->nonEmptyList->deleteElement($elm1);
        $this->assertTrue($this->nonEmptyList->isInList($elm2));
        $this->assertEquals(0, $this->nonEmptyList->getElementOrder($elm2));
        $this->assertTrue($elm2 === $this->nonEmptyList->getElementAt(0));
    }

    public function testCreateElementWillExist()
    {
        $this->assertTrue($this->emptyList->isInList($this->emptyList->createElement()));
    }

    public function testStubIsNotInList()
    {
        $this->assertFalse($this->emptyList->isInList(new StubOrderedListElementImpl));
    }


    public function testDetIdWIllReturnId(){
        $this->assertEquals($this->nonEmptyListId, $this->nonEmptyList->getId());
        $this->assertEquals($this->emptyListId, $this->emptyList->getId());
    }

} 