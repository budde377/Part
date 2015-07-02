<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:08 PM
 */
namespace ChristianBudde\Part\test;


use ChristianBudde\Part\controller\json\MailMailboxObjectImpl;
use ChristianBudde\Part\model\mail\DomainLibraryImpl;
use ChristianBudde\Part\model\mail\Mailbox;
use ChristianBudde\Part\model\mail\MailboxImpl;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubConfigImpl;
use ChristianBudde\Part\test\stub\StubDBImpl;
use ChristianBudde\Part\test\stub\StubObserverImpl;
use ChristianBudde\Part\test\stub\StubUserLibraryImpl;
use ChristianBudde\Part\test\util\SerializeCustomDatabaseTestCase;

class MailMailboxImplTest extends SerializeCustomDatabaseTestCase
{


    private $config;
    private $db;
    private $domainLibrary;
    private $domain;
    /** @var  \ChristianBudde\Part\model\mail\AddressLibraryImpl */
    private $addressLibrary;
    private $address;
    /** @var  \ChristianBudde\Part\model\mail\MailboxImpl */
    private $mailbox;
    /** @var  \ChristianBudde\Part\model\mail\MailboxImpl */
    private $nonExistingMailbox;
    /** @var  MailboxImpl */
    private $mailbox2;
    /** @var  StubBackendSingletonContainerImpl */
    private $container;

    function __construct()
    {
        parent::__construct(dirname(__FILE__) . '/../mysqlXML/MailMailboxImplTest.xml', $this->mailbox);
    }

    public function setUp()
    {
        parent::setUp();
        $this->container = new StubBackendSingletonContainerImpl();

        $this->config = new StubConfigImpl();
        $this->config->setMailMysqlConnection(array(
            'user' => self::$mailMySQLOptions->getUsername(),
            'database' => self::$mailMySQLOptions->getDatabase(),
            'host' => self::$mailMySQLOptions->getHost()
        ));
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

        $this->container->setUserLibraryInstance(new StubUserLibraryImpl());

        $this->domainLibrary = new DomainLibraryImpl($this->container);
        $this->domain = $this->domainLibrary->getDomain('test.dk');
        $this->addressLibrary = $this->domain->getAddressLibrary();
        $this->address = $this->addressLibrary->getAddress('test');
        $this->mailbox = $this->address->getMailbox();

        $this->mailbox2 = new MailboxImpl($this->container, $this->address);
        $this->nonExistingMailbox = new MailboxImpl($this->container, $this->addressLibrary->getAddress('test2'));
    }


    public function testMailboxReturnsRightDefaultInstances()
    {
        $this->assertTrue($this->address === $this->mailbox->getAddress());
        $this->assertTrue($this->addressLibrary === $this->mailbox->getAddressLibrary());
        $this->assertTrue($this->domain === $this->mailbox->getDomain());
        $this->assertTrue($this->domainLibrary === $this->mailbox->getDomainLibrary());
    }

    public function testExistReturnsRight()
    {
        $this->assertTrue($this->mailbox->exists());
        $this->assertFalse($this->nonExistingMailbox->exists());
    }

    public function testDeleteDeletes()
    {
        $this->mailbox->delete();
        $this->assertFalse($this->mailbox->exists());
    }

    public function testExistsIsFresh()
    {
        $m = new MailboxImpl($this->container, $this->address);
        $m->delete();
        $this->assertFalse($this->mailbox->exists());

    }

    public function testCreateCreates()
    {
        $this->nonExistingMailbox->create();
        $this->assertTrue($this->nonExistingMailbox->exists());
    }


    public function testGetNameReturnsName()
    {
        $this->assertEquals('John Doe', $this->mailbox->getName());
    }

    public function testSetNameSetsName()
    {
        $this->mailbox->setName($n = 'Jane Doe');
        $this->assertEquals($n, $this->mailbox->getName());
    }

    public function testSetNameIsTrimmed()
    {
        $this->mailbox->setName(($n = 'Jane Doe') . ' ');
        $this->assertEquals($n, $this->mailbox->getName());
    }


    public function testVerifyPasswordSuccessWithHash()
    {
        $this->assertTrue($this->mailbox->checkPassword('password'));
        $this->assertFalse($this->mailbox->checkPassword('password2'));
    }

    public function testSetPasswordSetsPassword()
    {
        $this->mailbox->setPassword($p = 'somePassword');
        $this->assertTrue($this->mailbox->checkPassword($p));
        $this->assertFalse($this->mailbox->checkPassword($p . "2"));
    }


    public function testSetEmptyPasswordFails()
    {
        $this->mailbox->setPassword('');
        $this->assertTrue($this->mailbox->checkPassword('password'));
        $this->assertFalse($this->mailbox->checkPassword(''));
    }

    public function testPasswordIsTrimmed()
    {
        $this->mailbox->setPassword('password ');
        $this->assertTrue($this->mailbox->checkPassword('password'));
    }


    public function testSetPasswordIsPersistent()
    {
        $this->mailbox->setPassword('bob');
        $this->assertTrue($this->mailbox2->checkPassword('bob'));
    }

    public function testSetNameIsPersistent()
    {
        $this->mailbox->setName($n = "Bent");
        $this->assertEquals($n, $this->mailbox2->getName());

    }

    public function testTimesAreRight()
    {
        $this->assertEquals(strtotime("2000-01-04 12:00:00"), $this->mailbox->createdAt());
        $this->assertEquals(strtotime("2000-01-04 13:00:00"), $this->mailbox->lastModified());
    }


    public function testTimesAre0PrDefault()
    {
        $this->assertTrue($this->nonExistingMailbox->lastModified() === 0);
        $this->assertTrue($this->nonExistingMailbox->createdAt() === 0);
    }

    public function testChangeNameChangesTime()
    {
        $t = $this->mailbox->lastModified();
        $this->mailbox->setName("Bent");
        $this->assertGreaterThan($t, $this->mailbox->lastModified());
    }

    public function testPasswordNameChangesTime()
    {
        $t = $this->mailbox->lastModified();
        $this->mailbox->setPassword("Bob2000");
        $this->assertGreaterThan($t, $this->mailbox->lastModified());
    }

    public function testObserversAreCalledOnDelete()
    {
        $ob = new StubObserverImpl();
        $this->mailbox->attachObserver($ob);
        $this->mailbox->delete();
        $this->assertTrue($ob->hasBeenCalled());
        $this->assertEquals(Mailbox::EVENT_DELETE, $ob->getLastCallType());
        $this->assertTrue($ob->getLastCallSubject() === $this->mailbox);
    }

    public function testCreateUpdatesTimestamps()
    {
        $this->nonExistingMailbox->create();
        $this->assertGreaterThan(0, $this->nonExistingMailbox->lastModified());
        $this->assertGreaterThan(0, $this->nonExistingMailbox->createdAt());
    }

    public function testObserversCanUnSubscribe()
    {
        $ob = new StubObserverImpl();
        $this->mailbox->attachObserver($ob);
        $this->mailbox->attachObserver($ob);
        $this->mailbox->detachObserver($ob);
        $this->mailbox->delete();
        $this->assertFalse($ob->hasBeenCalled());

    }
    public function testReturnsRightJSONObject(){
        $this->assertEquals($o = new MailMailboxObjectImpl($this->mailbox), $this->mailbox->jsonObjectSerialize());
        $this->assertEquals($o->jsonSerialize(), $this->mailbox->jsonSerialize());
    }

    public function testGenerateTypeHandlerReusesInstance(){

        $this->assertEquals($this->mailbox, $this->mailbox->generateTypeHandler());
    }
}