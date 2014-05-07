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

    public function testGetElementAtWillReturnNullIfLessThanZero()
    {
        $this->assertTrue(null === $this->nonEmptyList->getElementAt(-1));
    }

    public function testGetElementAtWillReturnNullIfLargerThanMax()
    {
        $this->assertTrue(null ===$this->nonEmptyList->getElementAt(10));
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


    public function testListElementsWillReturnArrayOfRightFormat(){
        $list = $this->nonEmptyList->listElements();
        $this->assertArrayHasKey(0, $list);
        $this->assertArrayHasKey(1, $list);
        $this->assertEquals(2, count($list));
        $this->assertTrue($list[0] === $this->nonEmptyList->getElementAt(0));
        $this->assertTrue($list[1] === $this->nonEmptyList->getElementAt(1));
    }

    public function testCreateElementWillExist()
    {
        $this->assertTrue($this->emptyList->isInList($this->emptyList->createElement()));
    }

    public function testStubIsNotInList()
    {
        $this->assertFalse($this->emptyList->isInList(new StubOrderedListElementImpl));
    }

    public function testMoveUpWillMoveUp(){
        $this->nonEmptyList->createElement();
        $elm = $this->nonEmptyList->getElementAt(1);
        $this->assertEquals(1, $this->nonEmptyList->getElementOrder($elm));
        $this->nonEmptyList->moveUp($elm);
        $this->assertEquals(2, $this->nonEmptyList->getElementOrder($elm));
    }

    public function testMoveUpLastDoesNothing(){
        $e = $this->nonEmptyList->lastElement();
        $this->nonEmptyList->moveUp($e);
        $this->assertEquals(1, $this->nonEmptyList->getElementOrder($e));
    }

    public function testMoveUpNotInListDoesNothing(){
        $e = new StubOrderedListElementImpl();
        $this->nonEmptyList->moveUp($e);
        $this->assertEquals(2, $this->nonEmptyList->size());
        $this->assertFalse($this->nonEmptyList->isInList($e));
    }


    public function testMoveDownNotInListDoesNothing(){
        $e = new StubOrderedListElementImpl();
        $this->nonEmptyList->moveDown($e);
        $this->assertEquals(2, $this->nonEmptyList->size());
        $this->assertFalse($this->nonEmptyList->isInList($e));
    }


    public function testMoveDownWillMoveDown(){
        $elm = $this->nonEmptyList->createElement();
        $this->assertEquals(2, $this->nonEmptyList->getElementOrder($elm));
        $this->nonEmptyList->moveDown($elm);
        $this->assertEquals(1, $this->nonEmptyList->getElementOrder($elm));
    }

    public function testMoveDownFirstDoesNothing(){
        $e = $this->nonEmptyList->firstElement();
        $this->nonEmptyList->moveDown($e);
        $this->assertEquals(0, $this->nonEmptyList->getElementOrder($e));
    }

    public function testMoveDownIsPersistent(){
        $e = $this->nonEmptyList->createElement();
        $this->nonEmptyList->moveDown($e);
        $l = new OrderedListImpl($this->db, $this->nonEmptyListId);
        $e2 = $l->getElementAt(1);
        $this->assertEquals($e->getId(), $e2->getId());
    }


    public function testMoveUpIsPersistent(){
        $this->nonEmptyList->createElement();
        $e = $this->nonEmptyList->firstElement();
        $this->nonEmptyList->moveUp($e);
        $l = new OrderedListImpl($this->db, $this->nonEmptyListId);
        $e2 = $l->getElementAt(1);
        $this->assertEquals($e->getId(), $e2->getId());
    }

    public function testSetOrderSetsOrder(){
        $e = $this->nonEmptyList->createElement();
        $this->assertEquals(2, $this->nonEmptyList->getElementOrder($e));
        $this->nonEmptyList->setElementOrder($e, 0);
        $this->assertEquals(0, $this->nonEmptyList->getElementOrder($e));
    }

    public function testSetOrderIsPersistent(){
        $e = $this->nonEmptyList->createElement();
        $this->assertEquals(2, $this->nonEmptyList->getElementOrder($e));
        $this->nonEmptyList->setElementOrder($e, 0);
        $l = new OrderedListImpl($this->db, $this->nonEmptyListId);
        $e2 = $l->getElementAt(0);
        $this->assertEquals($e->getId(), $e2->getId());
    }

    public function testSetOrderOfElementNotInListDoesNothing(){
        $this->nonEmptyList->setElementOrder($s = new StubOrderedListElementImpl(), 0);
        $this->assertEquals(2, $this->nonEmptyList->size());
        $this->assertFalse($this->nonEmptyList->isInList($s));
    }

    public function testSetOrderLargerThanMaxSetsLargestPossible(){
        $this->nonEmptyList->createElement();
        $e  = $this->nonEmptyList->firstElement();
        $this->assertEquals(0, $this->nonEmptyList->getElementOrder($e));
        $this->nonEmptyList->setElementOrder($e, 10);
        $this->assertEquals(2, $this->nonEmptyList->getElementOrder($e));
    }

    public function testSetOrderSmallerThanMinSetsLargestPossible(){
        $e = $this->nonEmptyList->createElement();
        $this->assertEquals(2, $this->nonEmptyList->getElementOrder($e));
        $this->nonEmptyList->setElementOrder($e, -10);
        $this->assertEquals(0, $this->nonEmptyList->getElementOrder($e));
    }


    public function testIterator(){
        $l = array();
        foreach($this->nonEmptyList as $k=>$v){
            $l[$k] = $v;
        }
        $this->assertEquals(2, count($l));
        $this->assertArrayHasKey(0, $l);
        $this->assertArrayHasKey(1, $l);
        $this->assertTrue($l[0] === $this->nonEmptyList->getElementAt(0));
        $this->assertTrue($l[1] === $this->nonEmptyList->getElementAt(1));
    }


    public function testIteratorWillUpdateOnCreate(){
        $l = array();
        foreach($this->nonEmptyList as $k=>$v){
            $l[$k] = $v;
        }
        $this->assertEquals(2, count($l));
        $this->assertArrayHasKey(0, $l);
        $this->assertArrayHasKey(1, $l);
        $this->assertTrue($l[0] === $this->nonEmptyList->getElementAt(0));
        $this->assertTrue($l[1] === $this->nonEmptyList->getElementAt(1));
        $this->nonEmptyList->createElement();
        $l = array();
        foreach($this->nonEmptyList as $k=>$v){
            $l[$k] = $v;
        }
        $this->assertEquals(3, count($l));
        $this->assertArrayHasKey(0, $l);
        $this->assertArrayHasKey(1, $l);
        $this->assertArrayHasKey(2, $l);
        $this->assertTrue($l[0] === $this->nonEmptyList->getElementAt(0));
        $this->assertTrue($l[1] === $this->nonEmptyList->getElementAt(1));
        $this->assertTrue($l[2] === $this->nonEmptyList->getElementAt(2));


    }

    public function testIteratorWillUpdateOnMoveDown(){
        $l = array();
        foreach($this->nonEmptyList as $k=>$v){
            $l[$k] = $v;
        }
        $this->assertEquals(2, count($l));
        $this->assertArrayHasKey(0, $l);
        $this->assertArrayHasKey(1, $l);
        $this->assertTrue($l[0] === $this->nonEmptyList->getElementAt(0));
        $this->assertTrue($l[1] === $this->nonEmptyList->getElementAt(1));
        $this->nonEmptyList->moveDown($l[1]);
        $l = array();
        foreach($this->nonEmptyList as $k=>$v){
            $l[$k] = $v;
        }
        $this->assertEquals(2, count($l));
        $this->assertArrayHasKey(0, $l);
        $this->assertArrayHasKey(1, $l);
        $this->assertTrue($l[0] === $this->nonEmptyList->getElementAt(0));
        $this->assertTrue($l[1] === $this->nonEmptyList->getElementAt(1));


    }


    public function testIteratorWillUpdateOnMoveUp(){
        $l = array();
        foreach($this->nonEmptyList as $k=>$v){
            $l[$k] = $v;
        }
        $this->assertEquals(2, count($l));
        $this->assertArrayHasKey(0, $l);
        $this->assertArrayHasKey(1, $l);
        $this->assertTrue($l[0] === $this->nonEmptyList->getElementAt(0));
        $this->assertTrue($l[1] === $this->nonEmptyList->getElementAt(1));
        $this->nonEmptyList->moveUp($l[0]);
        $l = array();
        foreach($this->nonEmptyList as $k=>$v){
            $l[$k] = $v;
        }
        $this->assertEquals(2, count($l));
        $this->assertArrayHasKey(0, $l);
        $this->assertArrayHasKey(1, $l);
        $this->assertTrue($l[0] === $this->nonEmptyList->getElementAt(0));
        $this->assertTrue($l[1] === $this->nonEmptyList->getElementAt(1));


    }

    public function testIteratorWillUpdateOnDelete(){
        $l = array();
        foreach($this->nonEmptyList as $k=>$v){
            $l[$k] = $v;
        }
        $this->assertEquals(2, count($l));
        $this->assertArrayHasKey(0, $l);
        $this->assertArrayHasKey(1, $l);
        $this->assertTrue($l[0] === $this->nonEmptyList->getElementAt(0));
        $this->assertTrue($l[1] === $this->nonEmptyList->getElementAt(1));
        $this->nonEmptyList->deleteElement($l[0]);
        $l = array();
        foreach($this->nonEmptyList as $k=>$v){
            $l[$k] = $v;
        }
        $this->assertEquals(1, count($l));
        $this->assertArrayHasKey(0, $l);
        $this->assertTrue($l[0] === $this->nonEmptyList->getElementAt(0));


    }
    public function testDetIdWIllReturnId(){
        $this->assertEquals($this->nonEmptyListId, $this->nonEmptyList->getId());
        $this->assertEquals($this->emptyListId, $this->emptyList->getId());
    }

} 