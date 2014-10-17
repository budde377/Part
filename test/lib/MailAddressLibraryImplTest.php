<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:08 PM
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\json\MailAddressLibraryObjectImpl;
use ChristianBudde\cbweb\model\mail\AddressLibrary;
use ChristianBudde\cbweb\model\mail\AddressLibraryImpl;
use ChristianBudde\cbweb\model\mail\DomainLibraryImpl;
use ChristianBudde\cbweb\model\mail\AddressImpl;
use ChristianBudde\cbweb\test\stub\StubUserLibraryImpl;
use ChristianBudde\cbweb\test\util\CustomDatabaseTestCase;
use ChristianBudde\cbweb\test\stub\StubConfigImpl;
use ChristianBudde\cbweb\test\stub\StubDBImpl;

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

    function __construct()
    {
        parent::__construct(dirname(__FILE__) . '/../mysqlXML/MailAddressLibraryImplTest.xml');
    }

    protected function setUp()
    {
        parent::setUp();

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

        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->userLibrary = new StubUserLibraryImpl();
        $this->domainLibrary = new DomainLibraryImpl($this->config, $this->db, $this->userLibrary);
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
        /** @var \ChristianBudde\cbweb\model\mail\AddressImpl $i2 */
        $i2 = $l['test2'];
        $this->assertInstanceOf('ChristianBudde\cbweb\model\mail\AddressImpl', $i1);
        $this->assertInstanceOf('ChristianBudde\cbweb\model\mail\AddressImpl', $i2);

        $this->assertEquals('test', $i1->getLocalPart());
        $this->assertEquals('test2', $i2->getLocalPart());
    }

    public function testGetReturnsRightInstance()
    {
        $this->assertInstanceOf('ChristianBudde\cbweb\model\mail\AddressImpl', $this->addressLibrary->getAddress('test'));
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
        $this->assertInstanceOf('ChristianBudde\cbweb\model\mail\AddressImpl', $adr);
        $this->assertEquals('', $adr->getLocalPart());
    }

    public function testHasAddressWillReturnRightBool()
    {
        $this->assertTrue($this->addressLibrary->hasAddress('test'));
        $this->assertFalse($this->addressLibrary->hasAddress('non-existing'));

    }

    public function testContainsReturnRightBool()
    {
        $a = new AddressImpl('test', $this->db, new StubUserLibraryImpl(), $this->addressLibrary);
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
        $this->assertInstanceOf('ChristianBudde\cbweb\model\mail\AddressImpl', $a);
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
        $a = new AddressImpl('test', $this->db, $this->userLibrary, $this->addressLibrary);
        $this->addressLibrary->deleteAddress($a);
        $this->assertTrue($a->exists());
    }

    public function testRenameObjectIsOK()
    {
        $a = $this->addressLibrary->getAddress('test');
        $a->setLocalPart('test3');
        $this->assertTrue($this->addressLibrary->hasAddress('test3'));
        $this->assertFalse($this->addressLibrary->hasAddress('test'));
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

    public function testCreateCatchallAddressCreates()
    {
        $c1 = $this->addressLibrary->getCatchallAddress();
        $this->addressLibrary->deleteCatchallAddress();
        $this->assertNull($this->addressLibrary->getCatchallAddress());
        $this->addressLibrary->createCatchallAddress();
        $this->assertTrue($this->addressLibrary->hasCatchallAddress());
        $c2 = $this->addressLibrary->getCatchallAddress();
        $this->assertEquals('', $c2->getLocalPart());
        $this->assertFalse($c1 === $c2);
        $this->assertTrue($c2->exists());
    }


    public function testHasAddressLocalPartWillBeTrimmed()
    {

        $this->assertTrue($this->addressLibrary->hasAddress('test '));
    }
    public function testReturnsRightJSONObject(){
        $this->assertEquals($o = new MailAddressLibraryObjectImpl($this->addressLibrary), $this->addressLibrary->jsonObjectSerialize());
        $this->assertEquals($o->jsonSerialize(), $this->addressLibrary->jsonSerialize());
    }
}