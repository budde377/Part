<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:08 PM
 */
use ChristianBudde\cbweb\MailAddressLibrary;
use ChristianBudde\cbweb\MailAddressImpl;
use ChristianBudde\cbweb\MailDomainLibraryImpl;
use ChristianBudde\cbweb\MailAddress;

class MailAddressImplTest extends CustomDatabaseTestCase{

    private $config;
    /** @var  MailAddressLibrary */
    private $addressLibrary;
    /** @var  MailAddressImpl */
    private $address;
    /** @var  MailAddressImpl */
    private $nonExistingAddress;

    private $db;
    private $domainLibrary;
    private $domain;
    private $mailPass;
    /** @var  MailAddressImpl */
    private $address2;


    function __construct()
    {
        parent::__construct(dirname(__FILE__).'/mysqlXML/MailAddressImplTest.xml');
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

        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->domainLibrary = new MailDomainLibraryImpl($this->config, $this->db);
        $this->domain = $this->domainLibrary->getDomain('test.dk');
        $this->addressLibrary = $this->domain->getAddressLibrary();
        $this->address = $this->addressLibrary->getAddress('test');
        $this->address2 = $this->addressLibrary->getAddress('test2');
        $this->nonExistingAddress = $this->createAddress('test3');
    }


    public function testGetLocalPartWillReturnLocalPart(){
        $this->assertEquals('test', $this->address->getLocalPart());
    }

    public function testGetAddressLibraryWillReturnRightInstance(){
        $this->assertTrue($this->addressLibrary === $this->address->getAddressLibrary());
    }

    public function testDomainWillReturnRightInstance(){
        $this->assertTrue($this->domain === $this->address->getDomain());
    }

    public function testDomainLibraryWillReturnRightInstance(){
        $this->assertTrue($this->domainLibrary === $this->address->getDomainLibrary());
    }

    public function testSetLocalPartWillSet(){
        $this->assertTrue($this->address->setLocalPart('asd'));
        $this->assertEquals('asd',$this->address->getLocalPart());
    }

    public function testSetLocalPartWillTrim(){
        $this->assertTrue($this->address->setLocalPart('asd '));
        $this->assertEquals('asd',$this->address->getLocalPart());
    }

    public function testSetOnlyAllowingValidDomains(){
        $this->assertFalse($this->address->setLocalPart('asd aas¨å åå'));
        $this->assertEquals('test',$this->address->getLocalPart());

    }

    public function testIsActiveWillReturnActive(){
        $this->assertTrue($this->address->isActive());
        $this->assertFalse($this->address2->isActive());
    }

    public function testDefaultIsActive(){
        $adr = $this->nonExistingAddress;
        $this->assertTrue($adr->isActive());
    }

    public function testActivateActivates(){
        $this->address2->activate();
        $this->assertTrue($this->address2->isActive());
    }


    public function testDeactivateDeactivates(){
        $this->address->deactivate();
        $this->assertFalse($this->address->isActive());
    }

    public function testLastModifiedAndCreatedIs0AsDefault(){
        $adr = $this->nonExistingAddress;
        $this->assertEquals(0, $adr->lastModified());
        $this->assertTrue(0 === $adr->lastModified());
        $this->assertEquals(0, $adr->createdAt());
        $this->assertTrue(0 === $adr->createdAt());
    }


    public function testLastModifiedIsSet(){
        $this->assertEquals(strtotime("2000-01-04 13:00:00"), $this->address->lastModified());
        $this->assertEquals(strtotime("2000-01-04 12:00:00"), $this->address->createdAt());
        $this->assertEquals(strtotime("2000-01-03 13:00:00"), $this->address2->lastModified());
        $this->assertEquals(strtotime("2000-01-03 12:00:00"), $this->address2->createdAt());
    }


    public function testHasMailboxReturnsRightResult(){
        $this->assertTrue($this->address->hasMailbox());
        $this->assertFalse($this->address2->hasMailbox());
    }

    public function testGetMailBoxReturnsRightInstance(){
        $mb1 = $this->address->getMailbox();
        $mb2 = $this->address2->getMailbox();
        $this->assertInstanceOf('ChristianBudde\cbweb\MailMailboxImpl', $mb1);
        $this->assertNull($mb2);

    }

    public function testExistsIsRight(){
        $this->assertTrue($this->address->exists());
        $this->assertTrue($this->address2->exists());
        $this->assertFalse($this->nonExistingAddress->exists());
    }

    public function testDeleteDeletes(){
        $this->address->delete();
        $this->assertFalse($this->address->exists());
    }

    public function testDeleteNonExistingIsOkay(){
        $this->nonExistingAddress->delete();
        $this->assertFalse($this->nonExistingAddress->exists());
    }

    public function testExistingIsFresh(){
        $adr = $this->createAddress('test');
        $this->assertTrue($this->address->exists());
        $adr->delete();
        $this->assertFalse($this->address->exists());
    }

    public function testCreateCreates(){
        $adr = $this->createAddress('test3');
        $adr->create();
        $this->assertTrue($adr->exists());
    }

    public function testCreateExistingDoesNothing(){
        $this->address->create();
        $this->assertTrue($this->address->exists());
    }

    public function testCreateWillUpdateTimes(){
        $this->assertEquals(0, $this->nonExistingAddress->lastModified());
        $this->assertEquals(0, $this->nonExistingAddress->createdAt());
        $this->nonExistingAddress->create();
        $this->assertGreaterThan(0, $this->nonExistingAddress->lastModified());
        $this->assertGreaterThan(0, $this->nonExistingAddress->createdAt());

    }

    public function testActivateIsPersistent(){
        $this->address2->activate();
        $adr = $this->createAddress('test2');
        $this->assertTrue($adr->isActive());
    }

    public function testDeactivateIsPersistent(){
        $this->address->deactivate();
        $adr = $this->createAddress('test');
        $this->assertFalse($adr->isActive());
    }

    public function testSetLocalPartIsPersistent(){
        $this->assertTrue($this->address->setLocalPart('test3'));
        $adr = $this->createAddress('test');
        $adr2 = $this->createAddress('test3');
        $this->assertFalse($adr->exists());
        $this->assertTrue($adr2->exists());

    }

    public function testSetLocalPartCannotSetToExisting(){
        $this->assertFalse($this->address->setLocalPart('test2'));
        $this->assertEquals('test',$this->address->getLocalPart());
    }


    public function testSetLocalPartUpdatesLastModified(){
        $t = $this->address->lastModified();
        $this->address->setLocalPart('test3');
        $this->assertGreaterThan($t, $this->address->lastModified());
    }

    public function testDeactivateUpdatesLastModified(){
        $t = $this->address->lastModified();
        $this->address->deactivate();
        $this->assertGreaterThan($t, $this->address->lastModified());
    }

    public function testActivateUpdatesLastModified(){
        $t = $this->address->lastModified();
        $this->address->deactivate();
        $this->assertGreaterThan($t, $this->address->lastModified());
    }

    public function testFailedSetLocalPartDoesNotUpdateLastModified(){
        $t = $this->address->lastModified();
        $this->address->setLocalPart('test2');
        $this->assertEquals($t, $this->address->lastModified());
    }

    public function testCreateMailboxOfNonExistingIsNotPossible(){
        $m = $this->nonExistingAddress->createMailbox("Arne", "password");
        $this->assertNull($m);
    }

    public function testCreateOnExitingReturnsRightInstance(){
        $this->assertTrue($this->address->createMailbox("BOB BOBBESEN", "password") === $this->address->getMailbox());
    }

    public function testCreateMailboxWillCreate(){
        $mb = $this->address2->createMailbox($n = "Bent", $p = "BentsPass");
        $this->assertInstanceOf('ChristianBudde\cbweb\MailMailboxImpl', $mb);
        $this->assertEquals($n, $mb->getName());
        $this->assertTrue($mb->checkPassword($p));
        $this->assertTrue($mb->exists());
        $this->assertTrue($mb === $this->address2->createMailbox("BOB", "HackerBob"));
    }

    public function testDeleteMailboxWillDelete(){
        $mb = $this->address->getMailbox();
        $this->address->deleteMailbox();
        $this->assertFalse($this->address->hasMailbox());
        $this->assertFalse($mb->exists());
    }

    public function testDeleteMailboxWillDeleteInLib(){
        $mb = $this->address->getMailbox();
        $mb->delete();
        $this->assertFalse($this->address->hasMailbox());
        $this->assertFalse($mb->exists());
    }

    public function testGetTargetsReturnArrayOfTargets(){
        $ar = $this->address2->getTargets();
        $this->assertTrue(is_array($ar));
        $this->assertEquals(2, count($ar));
        $this->assertArrayHasKey('test2@example.org', $ar);
        $this->assertArrayHasKey('test@example.org', $ar);
        $this->assertEquals('test@example.org', $ar['test@example.org']);
        $this->assertEquals('test2@example.org', $ar['test2@example.org']);

    }


    public function testHasTargetReturnsRightBool(){
        $this->assertTrue($this->address2->hasTarget('test@example.org'));
        $this->assertFalse($this->address2->hasTarget('no@example.org'));
    }

    public function testHasTargetWillTrimg(){
        $this->assertTrue($this->address2->hasTarget('test@example.org '));
    }

    public function testRemoveTargetWillRemoveIt(){
       $this->address2->removeTarget('test@example.org');
        $this->assertFalse($this->address2->hasTarget('test@example.org'));
    }

    public function testRemoveTargetWillTrimAndRemoveIt(){
       $this->address2->removeTarget(' test@example.org');
        $this->assertFalse($this->address2->hasTarget('test@example.org'));
    }


    public function testRemoveTargetIsPersistent(){
        $this->address2->removeTarget('test@example.org');
        $adr = $this->createAddress('test2');
        $this->assertFalse($adr->hasTarget('test@example.org'));
    }


    public function testRemoveTargetUpdatesLastModified(){
        $t = $this->address2->lastModified();
        $this->address2->removeTarget('test@example.org');

        $this->assertGreaterThan($t, $this->address2->lastModified());
    }

    public function testRemoveWillDoNothingIfTargetDoesNotExist(){
        $t = $this->address->lastModified();
        $this->address->removeTarget("target@target.dk");
        $this->assertEquals($t , $this->address->lastModified());
    }

    public function testAddTargetAddsATarget(){
        $this->address->addTarget($t = 'test@test.dk');
        $this->assertArrayHasKey($t, $ta = $this->address->getTargets());
        $this->assertEquals($t, $ta[$t]);
    }

    public function testAddTargetWillDoNothingIfTargetExists(){
        $t = $this->address->lastModified();
        $this->address->addTarget('test@example.org');
        $this->assertEquals($t, $this->address->lastModified());
    }

    public function testAddTargetWillNotAddInvalidEmail(){
        $this->address->addTarget('inva lid email');
        $this->assertFalse($this->address->hasTarget('inva lid email'));
    }


    public function testAddTargetWillTrim(){
        $this->address->addTarget(($t = 'test@test.dk').' ');
        $this->assertArrayHasKey($t, $ta = $this->address->getTargets());
        $this->assertEquals($t, $ta[$t]);
    }

    public function testAddTargetWillBePersistent(){
        $this->address->addTarget('test@example.org');
        $adr = $this->createAddress('test');
        $this->assertTrue($adr->hasTarget('test@example.org'));
    }


    public function testClearTargetWillClearTargets(){
        $this->address2->clearTargets();
        $this->assertEquals(0, count($this->address2->getTargets()));
    }

    public function testClearTargetsWillBePersistent(){
        $this->address2->clearTargets();
        $adr = $this->createAddress('test2');
        $this->assertEquals(0, count($adr->getTargets()));

    }

    public function testObserversWillBeNotifiedOnDelete(){
        $ob = new StubObserverImpl();

        $this->address->attachObserver($ob);
        $this->address->delete();
        $this->assertTrue($ob->hasBeenCalled());
        $this->assertEquals(MailAddress::EVENT_DELETE, $ob->getLastCallType());
    }

    public function testObserversWillBeNotNotifiedOnDeleteAfterDetachment(){
        $ob = new StubObserverImpl();

        $this->address->attachObserver($ob);
        $this->address->attachObserver($ob);
        $this->address->detachObserver($ob);
        $this->address->delete();
        $this->assertFalse($ob->hasBeenCalled());

    }


    public function testObservesWillNotBeCalledOnFailedDelete(){
        $ob = new StubObserverImpl();
        $this->nonExistingAddress->attachObserver($ob);
        $this->nonExistingAddress->delete();
        $this->assertFalse($ob->hasBeenCalled());
    }


    public function testObservesWillNotBeCalledOnFailedUpdateLocalPart(){
        $ob = new StubObserverImpl();
        $this->address->attachObserver($ob);
        $this->address->setLocalPart("test2");
        $this->assertFalse($ob->hasBeenCalled());
    }

    public function testObserversWillBeNotifiedOnChangeLocalPart(){
        $ob = new StubObserverImpl();

        $this->address->attachObserver($ob);
        $this->address->setLocalPart('test3');
        $this->assertTrue($ob->hasBeenCalled());
        $this->assertEquals(MailAddress::EVENT_CHANGE_LOCAL_PART, $ob->getLastCallType());
    }

    public function testGetIdReturnsId(){
        $this->assertEquals('addressId2',$this->address->getId());
        $this->address->setLocalPart('test3');
        $this->assertEquals('addressId2',$this->address->getId());
    }



    /**
     * @param string $string
     * @return MailAddressImpl
     */
    private function createAddress($string)
    {
        return new MailAddressImpl($string, $this->db, $this->addressLibrary);
    }


} 