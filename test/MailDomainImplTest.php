<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/7/14
 * Time: 1:50 PM
 */

class MailDomainImplTest extends CustomDatabaseTestCase{

    /** @var  MailDomainImpl */
    private $domain;
    /** @var  Config */
    private $config;
    /** @var  DB */
    private $db;


    function __construct()
    {
        parent::__construct(dirname(__FILE__).'/mysqlXML/MailDomainImplTest.xml');
    }


    public function setUp(){
        parent::setUp();
        $this->config = new StubConfigImpl();
        $this->config->setMailMysqlConnection(array(
            'user'=>self::$mailMySQLOptions->getUsername(),
            'database'=>self::$mailMySQLOptions->getDatabase(),
            'host'=>self::$mailMySQLOptions->getHost()
        ));
        $this->config->setMysqlConnection(array(
            'user'=>self::$mysqlOptions->getUsername(),
            'database'=>self::$mysqlOptions->getDatabase(),
            'host'=>self::$mysqlOptions->getHost(),
            'password'=>self::$mysqlOptions->getPassword()
        ));
        $this->db = new MySQLDBImpl($this->config);
        $this->domain = new MailDomainImpl('test.dk',$this->db, new StubMailDomainLibraryImpl());
    }


    public function testGetDomainNameReturnsDomainName(){
        $this->assertEquals('test.dk', $this->domain->getDomainName());
    }


    public function testIsActiveReflectsIfItIsActive(){
        $this->assertTrue($this->domain->isActive());
    }

    public function testActiveDoesActivate(){
        $this->domain->deactivate();
        $this->domain->activate();
        $this->assertTrue($this->domain->isActive());
    }

    public function testDeactivateDoesDeactivate(){
        $this->domain->deactivate();
        $this->assertFalse($this->domain->isActive());
    }

    public function testGetDescReturnsDesc(){
        $this->assertEquals("Some desc.", trim($this->domain->getDescription()));
    }

    public function testSetDescSetsDesc(){
        $newDesc = "NEW DESC";
        $this->domain->setDescription($newDesc);
        $this->assertEquals($newDesc, $this->domain->getDescription());
    }



} 