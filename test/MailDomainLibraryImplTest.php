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
    private $mailPass;
    private $databaseName;


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
        $this->mailPass = self::$mailMySQLOptions->getPassword();
        $this->databaseName = self::$mailMySQLOptions->getDatabase();

        $this->config->setMysqlConnection(array(
            'user'=>self::$mysqlOptions->getUsername(),
            'database'=>self::$mysqlOptions->getDatabase(),
            'host'=>self::$mysqlOptions->getHost(),
            'password'=>self::$mysqlOptions->getPassword()
        ));
        $this->db = new MySQLDBImpl($this->config);
        $this->domainLibrary = new MailDomainLibraryImpl($this->config, $this->db);
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
        $this->assertInstanceOf('MailDomainImpl', $d);

    }

    public function testGetDomainWillReuseInstance(){
        $d = $this->domainLibrary->getDomain('test.dk');
        $d2 = $this->domainLibrary->getDomain('test.dk');
        $this->assertTrue($d === $d2);
    }

    public function testGetDomainReturnNullIfNoDomain(){
        $this->assertNull($this->domainLibrary->getDomain('non-existing.dk'));
    }

    public function testGetDomainAndListReusesInstances(){
        $this->assertTrue($this->domainLibrary->getDomain('test.dk') === $this->domainLibrary->listDomains()['test.dk']);
    }

    public function testContainsDomainFailsOnDifferentDomain(){
        $this->assertFalse($this->domainLibrary->containsDomain(new MailDomainImpl('test.dk', $this->databaseName, $this->db, $this->domainLibrary)));
    }

    public function testContainsReturnsTrueIfContains(){
        $this->assertTrue($this->domainLibrary->containsDomain($this->domainLibrary->getDomain('test.dk')));
    }

    public function testCreateDomainCreatesANewDomain(){
        $d = $this->domainLibrary->createDomain('test2.dk', $this->mailPass);
        $this->assertTrue($d->exists());
    }

    public function testCreateDomainReusesInstances(){
        $d = $this->domainLibrary->createDomain('test.dk', $this->mailPass);
        $this->assertTrue($this->domainLibrary->getDomain('test.dk') === $d);

    }

    public function testListReusesNewInstances(){
        $d = $this->domainLibrary->createDomain('test2.dk', $this->mailPass);
        $this->assertTrue($this->domainLibrary->getDomain('test2.dk') === $d);

    }


    public function testDeleteDomainDoesThat(){
        $d = $this->domainLibrary->getDomain('test.dk');
        $this->domainLibrary->deleteDomain($d, $this->mailPass);
        $this->assertFalse($this->domainLibrary->containsDomain($d));
        $this->assertFalse($d->exists());
    }

    public function testDeleteDomainEffectsLibrary(){
        $d = $this->domainLibrary->getDomain('test.dk');
        $d->delete($this->mailPass);
        $this->assertFalse($this->domainLibrary->containsDomain($d));
        $this->assertFalse($d->exists());
    }

    public function testWillNotDeleteInstancesNotInLibrary(){
        $d = new MailDomainImpl('test.dk', $this->databaseName, $this->db, $this->domainLibrary);
        $this->domainLibrary->deleteDomain($d, $this->mailPass);
        $this->assertTrue($d->exists());
    }

} 