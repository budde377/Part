<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/5/14
 * Time: 4:06 PM
 */

class OrderedListElementTest extends CustomDatabaseTestCase{


    /** @var  DB */
    private $db;
    /** @var  OrderedListElementImpl */
    private $elm1;
    /** @var  OrderedListElementImpl */
    private $elm2;

    function __construct()
    {
        parent::__construct(dirname(__FILE__) . '/mysqlXML/PageContentImplTest.xml');
    }


    public function setUp(){
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->elm1 = new OrderedListElementImpl($this->db,"1");
        $this->elm2 = new OrderedListElementImpl($this->db, "2");

    }

    public function testGetIdMatchesId(){
        $this->assertEquals("1", $this->elm1->getId());
        $this->assertEquals("2", $this->elm2->getId());
    }

    public function testIfNoIdUniqueIsGenerated(){
        $elm1 = new OrderedListElementImpl($this->db);
        $elm2 = new OrderedListElementImpl($this->db);
        $this->assertFalse($elm1->getId() == $elm2->getId());
    }

} 