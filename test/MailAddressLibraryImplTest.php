<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:08 PM
 */

class MailAddressLibraryImplTest extends CustomDatabaseTestCase{


    private $config;
    /** @var  MailAddressLibrary */
    private $addressLibrary;
    private $db;
    private $domainLibrary;
    private $domain;
    private $mailPass;

    function __construct()
    {
        parent::__construct(dirname(__FILE__).'/mysqlXML/MailAddressLibraryImplTest.xml');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->config = new StubConfigImpl();
        $this->config->setMailMysqlConnection(array(
            'user'=>self::$mailMySQLOptions->getUsername(),
            'database'=>self::$mailMySQLOptions->getDatabase(),
            'host'=>self::$mailMySQLOptions->getHost()
        ));
        $this->mailPass = self::$mailMySQLOptions->getPassword();
        $this->config->setMysqlConnection(array(
            'user'=>self::$mysqlOptions->getUsername(),
            'database'=>self::$mysqlOptions->getDatabase(),
            'host'=>self::$mysqlOptions->getHost(),
            'password'=>self::$mysqlOptions->getPassword()
        ));

        $this->db = new MySQLDBImpl($this->config);

        $this->domainLibrary = new MailDomainLibraryImpl($this->config, $this->db);
        $this->domain = $this->domainLibrary->getDomain('test.dk');
        $this->addressLibrary = $this->domain->getAddressLibrary();
    }

    public function testGetDomainLibraryReturnsRightInstance(){
        $this->assertTrue($this->domainLibrary === $this->addressLibrary->getDomainLibrary());
    }

    public function testGetDomainReturnsRightInstance(){
        $this->assertTrue($this->domain === $this->addressLibrary->getDomain());
    }



}