<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:08 PM
 */
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\controller\json\MailAddressLibraryObjectImpl;
use ChristianBudde\Part\model\mail\AddressImpl;
use ChristianBudde\Part\model\mail\AddressLibraryImpl;
use ChristianBudde\Part\model\mail\DomainLibraryImpl;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubConfigImpl;
use ChristianBudde\Part\test\stub\StubDBImpl;
use ChristianBudde\Part\test\stub\StubUserLibraryImpl;
use ChristianBudde\Part\test\util\CustomDatabaseTestCase;

class MailAddressLibraryImplTest extends CustomDatabaseTestCase
{


    private $config;
    /** @var  AddressLibraryImpl */
    private $addressLibrary;
    private $db;
    private $domainLibrary;
    private $domain;
    private $mailPass;
    private $userLibrary;
    /** @var StubBackendSingletonContainerImpl  */
    private $container;

    function __construct()
    {
        parent::__construct(dirname(__FILE__) . '/../mysqlXML/MailAddressLibraryImplTest.xml');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->container = new StubBackendSingletonContainerImpl();

        $this->config = new StubConfigImpl();
        $this->config->setMailMysqlConnection(array(
            'user' => self::$mailMySQLOptions->getUsername(),
            'database' => self::$mailMySQLOptions->getDatabase(),
            'host' => self::$mailMySQLOptions->getHost()
        ));
        $this->mailPass = self::$mailMySQLOptions->getPassword();
        $this->config->setMysqlConnection(array(
            'user' => self::$mysqlOptions->getUsername(),
            'database' => self::$mysqlOptions->getDatabase(),
            'host' => self::$mysqlOptions->getHost(),
            'password' => self::$mysqlOptions->getPassword()
        ));
        $this->container->setConfigInstance($this->config);

        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->container->setDBInstance($this->db);

        $this->userLibrary = new StubUserLibraryImpl();
        $this->container->setUserLibraryInstance($this->userLibrary);

        $this->domainLibrary = new DomainLibraryImpl($this->container);
        $this->domain = $this->domainLibrary->getDomain('test.dk');
        $this->addressLibrary = $this->domain->getAddressLibrary();
    }

    public function testGetDomainLibraryReturnsRightInstance()
    {
        $this->assertTrue($this->domainLibrary === $this->addressLibrary->getDomainLibrary());
    }

    public function testGetDomainReturnsRightInstance()
    {
        $this->assertTrue($this->domain === $this->addressLibrary->getDomain());
    }

    public function testListAddressesReturnsRightResults()
    {
        $l = $this->addressLibrary->listAddresses();

        $this->assertTrue(is_array($l));

        $this->assertEquals(2, count($l));

        $this->assertArrayHasKey('test', $l);
        $this->assertArrayHasKey('test2', $l);

        /** @var AddressImpl $i1 */
        $i1 = $l['test'];
        /** @var \ChristianBudde\Part\model\mail\AddressImpl $i2 */
        $i2 = $l['test2'];
        $this->assertInstanceOf('ChristianBudde\Part\model\mail\AddressImpl', $i1);
        $this->assertInstanceOf('ChristianBudde\Part\model\mail\AddressImpl', $i2);

        $this->assertEquals('test', $i1->getLocalPart());
        $this->assertEquals('test2', $i2->getLocalPart());
    }

    public function testGetReturnsRightInstance()
    {
        $this->assertInstanceOf('ChristianBudde\Part\model\mail\AddressImpl', $this->addressLibrary->getAddress('test'));
        $this->assertTrue($this->addressLibrary->getAddress('test') === $this->addressLibrary->listAddresses()['test']);
    }

    public function testGetSpacesAreTrimmed()
    {
        $this->assertTrue($this->addressLibrary->getAddress('test ') === $this->addressLibrary->listAddresses()['test']);
    }

    public function testGetDoesNotReturnAnythingWithEmpty()
    {
        $this->assertNull($this->addressLibrary->getAddress(''));
    }

    public function testHasCatchallReturnsTrue()
    {
        $this->assertTrue($this->addressLibrary->hasCatchallAddress());
    }

    public function testGetCatchallAddressReturnsRightAddress()
    {
        $adr = $this->addressLibrary->getCatchallAddress();
        $this->assertInstanceOf('ChristianBudde\Part\model\mail\AddressImpl', $adr);
        $this->assertEquals('', $adr->getLocalPart());
    }

    public function testHasAddressWillReturnRightBool()
    {
        $this->assertTrue($this->addressLibrary->hasAddressWithLocalPart('test'));
        $this->assertFalse($this->addressLibrary->hasAddressWithLocalPart('non-existing'));

    }

    public function testContainsReturnRightBool()
    {
        $a = new AddressImpl($this->container, 'test', $this->addressLibrary);
        $this->assertTrue($this->addressLibrary->contains($this->addressLibrary->getAddress('test')));
        $this->assertFalse($this->addressLibrary->contains($a));

    }


    public function testCreateAddressReturnsExisting()
    {
        $this->assertTrue($this->addressLibrary->getAddress('test') === $this->addressLibrary->createAddress('test'));

    }

    public function testCreateReturnNewAddress()
    {
        $a = $this->addressLibrary->createAddress('test3');
        $this->assertTrue($a->exists());
        $this->assertInstanceOf('ChristianBudde\Part\model\mail\AddressImpl', $a);
        $this->assertEquals('test3', $a->getLocalPart());
        $this->assertTrue($this->addressLibrary->contains($a));
    }

    public function testDeleteAddressDeletesFromLibAndAddress()
    {
        $a = $this->addressLibrary->getAddress('test');
        $this->addressLibrary->deleteAddress($a);
        $this->assertFalse($a->exists());
        $this->assertFalse($this->addressLibrary->contains($a));

    }

    public function testDeleteAddressDeletesFromLib()
    {
        $a = $this->addressLibrary->getAddress('test');
        $a->delete();
        $this->assertFalse($this->addressLibrary->contains($a));

    }

    public function testDeleteFromInstanceNotInLibDoesNothing()
    {
        $a = new AddressImpl($this->container, 'test', $this->addressLibrary);
        $this->addressLibrary->deleteAddress($a);
        $this->assertTrue($a->exists());
    }

    public function testRenameObjectIsOK()
    {
        $a = $this->addressLibrary->getAddress('test');
        $a->setLocalPart('test3');
        $this->assertTrue($this->addressLibrary->hasAddressWithLocalPart('test3'));
        $this->assertFalse($this->addressLibrary->hasAddressWithLocalPart('test'));
    }

    public function testRenameIsOkWRTList()
    {
        $a = $this->addressLibrary->getAddress('test');
        $a->setLocalPart('test3');
        $ar = $this->addressLibrary->listAddresses();
        $this->assertArrayHasKey('test3', $ar);
        $this->assertArrayNotHasKey('test', $ar);


    }

    public function testDeleteCatchallAddressDeletesTheAddress()
    {
        $catchAll = $this->addressLibrary->getCatchallAddress();
        $this->addressLibrary->deleteCatchallAddress();
        $this->assertFalse($this->addressLibrary->hasCatchallAddress());
        $this->assertFalse($catchAll->exists());
    }

    public function testDeleteCatchallAddressDeletesTheAddressWithDeleteAddressFunction()
    {
        $catchAll = $this->addressLibrary->getCatchallAddress();
        $this->addressLibrary->deleteAddress($catchAll);
        $this->assertFalse($this->addressLibrary->hasCatchallAddress());
        $this->assertFalse($catchAll->exists());
    }

    public function testCreateCatchallAddressCreates()
    {
        $c1 = $this->addressLibrary->getCatchallAddress();
        $this->addressLibrary->deleteCatchallAddress();
        $this->assertNull($this->addressLibrary->getCatchallAddress());
        $c3 = $this->addressLibrary->createCatchallAddress();
        $this->assertTrue($this->addressLibrary->hasCatchallAddress());
        $c2 = $this->addressLibrary->getCatchallAddress();
        $this->assertEquals('', $c2->getLocalPart());
        $this->assertFalse($c1 === $c2);
        $this->assertTrue($c3 === $c2);
        $this->assertTrue($c2->exists());
    }

    public function testCreateCatchallAddressWhileExistingReturnsOldInstance()
    {
        $c1 = $this->addressLibrary->createCatchallAddress();
        $c2 = $this->addressLibrary->createCatchallAddress();
        $this->assertTrue($c1 === $c2);
    }


    public function testHasAddressLocalPartWillBeTrimmed()
    {

        $this->assertTrue($this->addressLibrary->hasAddressWithLocalPart('test '));
    }
    public function testReturnsRightJSONObject(){
        $this->assertEquals($o = new MailAddressLibraryObjectImpl($this->addressLibrary), $this->addressLibrary->jsonObjectSerialize());
        $this->assertEquals($o->jsonSerialize(), $this->addressLibrary->jsonSerialize());
    }

    public function testGenerateTypeHandlerReusesInstance(){

        $this->assertEquals($this->addressLibrary, $this->addressLibrary->generateTypeHandler());
    }
}