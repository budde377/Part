<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:08 PM
 */

class MailMailboxImplTest extends CustomDatabaseTestCase{



    private $config;
    private $db;
    private $domainLibrary;
    private $domain;
    /** @var  MailAddressLibraryImpl */
    private $addressLibrary;
    private $address;
    /** @var  MailMailboxImpl */
    private $mailbox;
    /** @var  MailMailboxImpl */
    private $nonExistingMailbox;
    /** @var  MailMailboxImpl */
    private $mailbox2;

    function __construct()
    {
        parent::__construct(dirname(__FILE__).'/mysqlXML/MailMailboxImplTest.xml');
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

        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->domainLibrary = new MailDomainLibraryImpl($this->config, $this->db);
        $this->domain = $this->domainLibrary->getDomain('test.dk');
        $this->addressLibrary = $this->domain->getAddressLibrary();
        $this->address = $this->addressLibrary->getAddress('test');
        $this->mailbox = $this->address->getMailbox();
        $this->mailbox2 = new MailMailboxImpl($this->address, $this->db);
        $this->nonExistingMailbox = new MailMailboxImpl($this->addressLibrary->getAddress('test2'), $this->db);
    }



    public function testMailboxReturnsRightDefaultInstances(){
        $this->assertTrue($this->address === $this->mailbox->getAddress());
        $this->assertTrue($this->addressLibrary === $this->mailbox->getAddressLibrary());
        $this->assertTrue($this->domain === $this->mailbox->getDomain());
        $this->assertTrue($this->domainLibrary === $this->mailbox->getDomainLibrary());
    }

    public function testExistReturnsRight(){
        $this->assertTrue($this->mailbox->exists());
        $this->assertFalse($this->nonExistingMailbox->exists());
    }

    public function testDeleteDeletes(){
        $this->mailbox->delete();
        $this->assertFalse($this->mailbox->exists());
    }

    public function testExistsIsFresh(){
        $m = new MailMailboxImpl($this->address, $this->db);
        $m->delete();
        $this->assertFalse($this->mailbox->exists());

    }

    public function testCreateCreates(){
        $this->nonExistingMailbox->create();
        $this->assertTrue($this->nonExistingMailbox->exists());
    }


    public function testGetNameReturnsName(){
        $this->assertEquals('John Doe', $this->mailbox->getName());
    }

    public function testSetNameSetsName(){
        $this->mailbox->setName($n = 'Jane Doe');
        $this->assertEquals($n, $this->mailbox->getName());
    }

    public function testSetNameIsTrimmed(){
        $this->mailbox->setName(($n = 'Jane Doe').' ');
        $this->assertEquals($n, $this->mailbox->getName());
    }


    public function testVerifyPasswordSuccessWithHash(){
        $this->assertTrue($this->mailbox->checkPassword('password'));
        $this->assertFalse($this->mailbox->checkPassword('password2'));
    }

    public function testSetPasswordSetsPassword(){
        $this->mailbox->setPassword($p = 'somePassword');
        $this->assertTrue($this->mailbox->checkPassword($p));
        $this->assertFalse($this->mailbox->checkPassword($p."2"));
    }


    public function testSetEmptyPasswordFails(){
        $this->mailbox->setPassword('');
        $this->assertTrue($this->mailbox->checkPassword('password'));
        $this->assertFalse($this->mailbox->checkPassword(''));
    }

    public function testPasswordIsTrimmed(){
        $this->mailbox->setPassword('password ');
        $this->assertTrue($this->mailbox->checkPassword('password'));
    }


    public function testSetPasswordIsPersistent(){
        $this->mailbox->setPassword('bob');
        $this->assertTrue($this->mailbox2->checkPassword('bob'));
    }

    public function testSetNameIsPersistent(){
        $this->mailbox->setName($n = "Bent");
        $this->assertEquals($n, $this->mailbox2->getName());

    }

    public function testTimesAreRight(){
        $this->assertEquals(strtotime("2000-01-04 12:00:00"), $this->mailbox->createdAt());
        $this->assertEquals(strtotime("2000-01-04 13:00:00"), $this->mailbox->lastModified());
    }


    public function testTimesAre0PrDefault(){
        $this->assertTrue($this->nonExistingMailbox->lastModified() === 0);
        $this->assertTrue($this->nonExistingMailbox->createdAt() === 0);
    }

    public function testChangeNameChangesTime(){
        $t = $this->mailbox->lastModified();
        $this->mailbox->setName("Bent");
        $this->assertGreaterThan($t, $this->mailbox->lastModified());
    }

    public function testPasswordNameChangesTime(){
        $t = $this->mailbox->lastModified();
        $this->mailbox->setPassword("Bob2000");
        $this->assertGreaterThan($t, $this->mailbox->lastModified());
    }

    public function testObserversAreCalledOnDelete(){
        $ob = new StubObserverImpl();
        $this->mailbox->attachObserver($ob);
        $this->mailbox->delete();
        $this->assertTrue($ob->hasBeenCalled());
        $this->assertEquals(MailMailbox::EVENT_DELETE, $ob->getLastCallType());
        $this->assertTrue($ob->getLastCallSubject() === $this->mailbox);
    }

    public function testCreateUpdatesTimestamps(){
        $this->nonExistingMailbox->create();
        $this->assertGreaterThan(0, $this->nonExistingMailbox->lastModified());
        $this->assertGreaterThan(0, $this->nonExistingMailbox->createdAt());
    }

    public function testObserversCanUnSubscribe(){
        $ob = new StubObserverImpl();
        $this->mailbox->attachObserver($ob);
        $this->mailbox->attachObserver($ob);
        $this->mailbox->detachObserver($ob);
        $this->mailbox->delete();
        $this->assertFalse($ob->hasBeenCalled());

    }

} 