<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/5/14
 * Time: 3:20 PM
 */

class OrderedListImplTest extends CustomDatabaseTestCase{


    /** @var  DB */
    private $db;
    /** @var  OrderedListImpl */
    private $nonEmptyList;
    /** @var  OrderedListImpl */
    private $emptyList;

    function __construct()
    {
        parent::__construct(dirname(__FILE__) . '/mysqlXML/OrderedListImplTest.xml');
    }


    public function setUp(){
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->nonEmptyList = new OrderedListImpl($this->db, "someList");
        $this->emptyList = new OrderedListImpl($this->db, "emptyList");

    }

    public function testSizeOfListReturnsRightSize(){
        $this->assertEquals(2, $this->nonEmptyList->size());
        $this->assertEquals(0,$this->emptyList->size());
    }

    public function testCreateElementWillCreateNewElement(){
        $elm = $this->nonEmptyList->createElement();
        $elm2 = $this->nonEmptyList->createElement();
        $this->assertInstanceOf("OrderedListElement",$elm);
        $this->assertInstanceOf("OrderedListElement",$elm2);
        $this->assertTrue($elm !== $elm2);
    }


    public function testCreateElementWillExist(){
        $this->assertTrue($this->emptyList->isInList($this->emptyList->createElement()));
    }

    public function testNullIsNotInList(){
        $this->assertFalse($this->emptyList->isInList(new StubOrderedListElementImpl));
    }


} 