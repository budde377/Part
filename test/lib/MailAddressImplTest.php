<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:08 PM
 */
use ChristianBudde\Part\model\mail\Address;
use ChristianBudde\Part\model\mail\AddressImpl;
use ChristianBudde\Part\model\mail\AddressLibrary;
use ChristianBudde\Part\model\mail\DomainLibraryImpl;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\model\user\UserLibrary;
use ChristianBudde\Part\model\user\UserLibraryImpl;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubConfigImpl;
use ChristianBudde\Part\test\stub\StubDBImpl;
use ChristianBudde\Part\test\stub\StubObserverImpl;
use ChristianBudde\Part\test\util\CustomDatabaseTestCase;

class MailAddressImplTest extends CustomDatabaseTestCase{

    private $config;
    /** @var  AddressLibrary */
    private $addressLibrary;
    /** @var  AddressImpl */
    private $address;
    /** @var  AddressImpl */
    private $nonExistingAddress;

    private $db;
    private $domainLibrary;
    private $domain;
    private $mailPass;
    /** @var  AddressImpl */
    private $address2;
    /** @var  User */
    private $owner1;
    /** @var  User */
    private $owner2;
    /** @var  UserLibrary */
    private $userLibrary;
    /** @var  StubBackendSingletonContainerImpl */
    private $container;

    function __construct()
    {
        parent::__construct(dirname(__FILE__).'/../mysqlXML/MailAddressImplTest.xml');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->container = new StubBackendSingletonContainerImpl();

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
        $this->container->setConfigInstance($this->config);

        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->container->setDBInstance($this->db);

        $this->userLibrary = new UserLibraryImpl($this->container);
        $this->container->setUserLibraryInstance($this->userLibrary);

        $this->domainLibrary = new DomainLibraryImpl($this->container);
        $this->domain = $this->domainLibrary->getDomain('test.dk');
        $this->addressLibrary = $this->domain->getAddressLibrary();
        $this->address = $this->addressLibrary->getAddress('test');
        $this->address2 = $this->addressLibrary->getAddress('test2');
        $this->nonExistingAddress = $this->createAddress('test3');
        $this->owner1 = $this->userLibrary->getUser('owner1');
        $this->owner2 = $this->userLibrary->getUser('owner2');

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
        $this->assertInstanceOf('ChristianBudde\Part\model\mail\MailboxImpl', $mb1);
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
        $this->assertInstanceOf('ChristianBudde\Part\model\mail\MailboxImpl', $mb);
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
        $this->assertEquals(['test2@example.org', 'test@example.org'], $ar);

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
        $this->assertContains($t, $this->address->getTargets());
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
        $this->assertContains($t, $this->address->getTargets());
    }


    public function testAddTargetWillBePersistent(){
        $this->address->addTarget('test@example.org');
        $adr = $this->createAddress('test');
        $this->assertTrue($adr->hasTarget('test@example.org'));
    }


    public function testTargetRemovedOnDelete(){
        $this->address->addTarget('test@example.org');
        $this->address->delete();
        $this->assertFalse($this->address->hasTarget('test@example.org'));
        $adr = $this->createAddress('test');
        $this->assertFalse($adr->hasTarget('test@example.org'));
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
        $this->assertEquals(Address::EVENT_DELETE, $ob->getLastCallType());
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
        $this->assertEquals(Address::EVENT_CHANGE_LOCAL_PART, $ob->getLastCallType());
    }

    public function testGetIdReturnsId(){
        $this->assertEquals('addressId2',$this->address->getId());
        $this->address->setLocalPart('test3');
        $this->assertEquals('addressId2',$this->address->getId());
    }

    public function testAddOwnerAddsOwner(){
        $this->address->addOwner($this->owner1);
        $this->address->addOwner($this->owner2);
        $list = $this->address->listOwners();
        $this->assertEquals([$this->owner1->getUsername(), $this->owner2->getUsername()], $list);
    }

    public function testIsOwnerIsTrueIfOwner(){
        $this->assertFalse($this->address->isOwner($this->owner1));
        $this->address->addOwner($this->owner1);
        $this->assertTrue($this->address->isOwner($this->owner1));
    }

    public function testRemoveOwnerRemovesOwner(){
        $this->address->addOwner($this->owner1);
        $this->assertTrue($this->address->isOwner($this->owner1));
        $this->address->removeOwner($this->owner1);
        $this->assertFalse($this->address->isOwner($this->owner1));
        $this->assertEquals(0, count($this->address->listOwners()));
    }

    public function testListOwnersWillUseUserLibraryIfProvided(){
        $this->address->addOwner($this->owner1);
        $list = $this->address->listOwners(true);
        $this->assertTrue($this->owner1 === $list[0]);
    }

    public function testListOwnersReturnsArray(){
        $list = $this->address->listOwners();
        $this->assertTrue(is_array($list));
        $this->assertEquals(0, count($list));
    }

    public function testIsOwnerIsUpToDateWithChangeOfUsername(){
        $this->address->addOwner($this->owner1);
        $this->owner1->setUsername("bob");
        $this->assertTrue($this->address->isOwner($this->owner1));
    }

    public function testAddRemoveUserAndUpdateUsernameIsOk(){
        $this->address->addOwner($this->owner1);
        $this->address->removeOwner($this->owner1);
        $this->owner1->setUsername("bob");
        $this->assertFalse($this->address->isOwner($this->owner1));
        $this->assertEquals(0, count($this->address->listOwners()));
    }

    public function testListOwnersIsUpToDateWithChangeOfUsername(){
        $this->address->addOwner($this->owner1);
        $this->owner1->setUsername("bob");
        $list = $this->address->listOwners(true);
        $this->assertTrue($this->owner1 === $list[0]);
    }

    public function testAddUserIsPersistent(){
        $this->address->addOwner($this->owner1);
        $address = $this->cloneAddress($this->address);
        $this->assertTrue($address->isOwner($this->owner1));

    }

    public function testAddUserIsPersistentAndInList(){
        $this->address->addOwner($this->owner1);
        $address = $this->cloneAddress($this->address);
        $this->assertContains($this->owner1, $address->listOwners(true));
        $this->assertContains($this->owner1->getUsername(), $address->listOwners());

    }

    public function testAddUserIsPersistentAndUsernameUpdateOk(){
        $this->address->addOwner($this->owner1);
        $address = $this->cloneAddress($this->address);
        $this->owner1->setUsername("bob");
        $this->assertTrue($address->isOwner($this->owner1));
    }

    public function testRemoveUserIsPersistent(){
        $this->address->addOwner($this->owner1);
        $this->address->removeOwner($this->owner1);
        $address = $this->cloneAddress($this->address);
        $this->assertFalse($address->isOwner($this->owner1));

    }


    public function testRemoveOwnerIsOKWhenNotOwner(){
        $this->address->removeOwner($this->owner1);
    }

    public function testAddOwnerWhenOwnerIsOk(){
        $this->address->addOwner($this->owner1);
        $this->address->addOwner($this->owner1);
    }

    public function testDeleteAddressRemovesOwners(){
        $this->address->addOwner($this->owner1);
        $this->address->delete();
        $this->assertEquals([], $this->address->listOwners());
    }

    public function testCantAddOwnersToNonExisting(){
        $this->address->delete();
        $this->address->addOwner($this->owner1);
        $this->assertEquals(0, count($this->address->listOwners()));
    }
    public function testGenerateTypeHandlerReusesInstance(){

        $this->assertEquals($this->address, $this->address->generateTypeHandler());
    }
    private function cloneAddress(Address $address){
        return $this->createAddress($address->getLocalPart());
    }
    public function testReturnsRightJSONObject(){
        $this->assertEquals($o = new \ChristianBudde\Part\controller\json\MailAddressObjectImpl($this->address), $this->address->jsonObjectSerialize());
        $this->assertEquals($o->jsonSerialize(), $this->address->jsonSerialize());
    }
    /**
     * @param string $string
     * @return AddressImpl
     */
    private function createAddress($string)
    {
        return new AddressImpl($this->container, $string, $this->addressLibrary);
    }


} 