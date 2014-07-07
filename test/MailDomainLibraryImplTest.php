<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/4/14
 * Time: 11:23 PM
 */

class MailDomainLibraryImplTest extends CustomDatabaseTestCase{

    /** @var  Config */
    private $config;
    /** @var  DB */
    private $db;
    /** @var  MailDomainLibraryImpl */
    private $domainLibrary;

    function __construct()
    {
        parent::__construct(dirname(__FILE__).'/mysqlXML/MailDomainLibraryImplTest.xml');
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
        $this->domainLibrary = new MailDomainLibraryImpl($this->db);
    }



    public function testListDomainsWillListDomains(){
        $domainArray = $this->domainLibrary->listDomains();
        $this->assertTrue(is_array($domainArray));
        $this->assertEquals(1,count($domainArray));
        $this->assertArrayHasKey('test.dk', $domainArray);
        $this->assertInstanceOf('MailDomain', $domainArray['test.dk']);
    }

    public function testListDomainsWillReuseInstances(){
        $domainArray = $this->domainLibrary->listDomains();
        $domainArray2 = $this->domainLibrary->listDomains();
        $this->assertTrue($domainArray['test.dk'] === $domainArray2['test.dk']);
    }


    public function testGetDomainWillGetDomain(){
        $d = $this->domainLibrary->getDomain('test.dk');
        $this->assertInstanceOf('MailDomain', $d);

    }

    public function testGetDomainWillReuseInstance(){
        $d = $this->domainLibrary->getDomain('test.dk');
        $d2 = $this->domainLibrary->getDomain('test.dk');
        $this->assertTrue($d === $d2);
    }

    public function testGetDomainReturnNullIfNoDomain(){
        $this->assertNull($this->domainLibrary->getDomain('non-existing.dk'));
    }


} 