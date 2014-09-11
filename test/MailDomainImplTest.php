<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/7/14
 * Time: 1:50 PM
 */
use ChristianBudde\cbweb\MailDomainImpl;
use ChristianBudde\cbweb\Config;
use ChristianBudde\cbweb\DB;
use ChristianBudde\cbweb\MySQLDBImpl;
use ChristianBudde\cbweb\MailDomain;

class MailDomainImplTest extends CustomDatabaseTestCase{

    /** @var  MailDomainImpl */
    private $domain;

    /** @var  MailDomainImpl */
    private $domain2;
    /** @var  MailDomainImpl */
    private $nonCreatedDomain;
    /** @var  Config */
    private $config;
    /** @var  DB */
    private $db;

    private $modTime;
    private $creaTime;
    /** @var  StubMailDomainLibraryImpl */
    private $domainLib;

    private $mailPass;
    private $databaseName;

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
        $this->mailPass = self::$mailMySQLOptions->getPassword();
        $this->config->setMysqlConnection(array(
            'user'=>self::$mysqlOptions->getUsername(),
            'database'=>self::$mysqlOptions->getDatabase(),
            'host'=>self::$mysqlOptions->getHost(),
            'password'=>self::$mysqlOptions->getPassword()
        ));
        $this->db = new MySQLDBImpl($this->config);
        $this->databaseName = self::$mysqlOptions->getDatabase();
        $this->domainLib = new StubMailDomainLibraryImpl();
        $this->domain = new MailDomainImpl('test.dk',$this->databaseName, $this->db, $this->domainLib);
        $this->domain2 = new MailDomainImpl('test2.dk',$this->databaseName, $this->db, $this->domainLib);
        $this->modTime = strtotime("2000-01-01 13:00:00");
        $this->creaTime = strtotime("2000-01-01 12:00:00");
        $this->nonCreatedDomain = new MailDomainImpl('non-existing.dk',$this->databaseName, $this->db, $this->domainLib);

    }


    public function testGetDomainNameReturnsDomainName(){
        $this->assertEquals('test.dk', $this->domain->getDomainName());
    }


    public function testIsActiveReflectsIfItIsActive(){
        $this->assertTrue($this->domain->isActive());
    }

    public function testDefaultForNonModifiedOrCreatedIsActive(){
        $this->assertTrue($this->nonCreatedDomain->isActive());
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

    public function testDescIsEmptyStringIfNewAndNotCreated(){
        $this->assertTrue($this->nonCreatedDomain->getDescription() === "");
    }

    public function testSetDescSetsDesc(){
        $newDesc = "NEW DESC";
        $this->domain->setDescription($newDesc);
        $this->assertEquals($newDesc, $this->domain->getDescription());
    }

    public function testLastModifiedIsRight(){
        $this->assertEquals($this->modTime, $this->domain->lastModified());
    }

    public function testLastModifiedIs0IfNotCreated(){
        $this->assertTrue(0 === $this->nonCreatedDomain->lastModified());
    }

    public function testCreatedTimeIsRight(){
        $this->assertEquals($this->creaTime, $this->domain->createdAt());
    }

    public function testCreatedAtIs0IfNotCreated(){
        $this->assertTrue(0 === $this->nonCreatedDomain->createdAt());
    }

    public function testExistsReturnsFalseIfDoesNotExists(){
        $this->assertFalse($this->nonCreatedDomain->exists());
    }

    public function testExistsReturnsTrueIfExists(){
        $this->assertTrue($this->domain->exists());
    }

    public function testCreateCreatesTheDomainWhenRightPassword(){
        $this->assertTrue($this->nonCreatedDomain->create($this->mailPass));
        $this->assertTrue($this->nonCreatedDomain->exists());
    }

    public function testCreateDoesNotCreateWithWrongPassword(){
        $this->assertFalse($this->nonCreatedDomain->create("wrongPass"));
        $this->assertFalse($this->nonCreatedDomain->exists());
    }


    public function testCreateExistingDoesNothing(){
        $this->assertTrue($this->domain->create($this->mailPass));
        $this->assertTrue($this->domain->exists());
    }

    public function testCreateUpdatesTheTimestamps(){
        $t1 = $this->nonCreatedDomain->createdAt();
        $t2 = $this->nonCreatedDomain->lastModified();
        $this->nonCreatedDomain->create($this->mailPass);
        $this->assertGreaterThan($t1, $this->nonCreatedDomain->createdAt());
        $this->assertGreaterThan($t2, $this->nonCreatedDomain->createdAt());
    }

    public function testExistsIsFresh(){
        $d = $this->cloneDomain($this->nonCreatedDomain);
        $this->assertFalse($this->nonCreatedDomain->exists());
        $this->assertTrue($d->create($this->mailPass));
        $this->assertTrue($this->nonCreatedDomain->exists());
    }



    public function testDeleteDoesDelete(){
        $this->assertTrue($this->domain->delete($this->mailPass));
        $this->assertFalse($this->domain->exists());
    }

    public function testDeleteDoesNotDeleteWithWrongPassword(){
        $this->assertFalse($this->domain->delete("wrongpass"));
        $this->assertTrue($this->domain->exists());
    }

    public function testDeleteNonExistingDoesNothing(){
        $this->assertTrue($this->nonCreatedDomain->delete($this->mailPass));
        $this->assertFalse($this->nonCreatedDomain->exists());
    }

    public function testGetDomainLibraryReturnsInstanceProvidedInConstructor(){
        $this->assertTrue($this->domainLib === $this->domain->getDomainLibrary());
    }

    public function testIsAliasDomainReturnsFalseWhenNot(){
        $this->assertFalse($this->domain->isAliasDomain());
    }

    public function testGetAliasTargetReturnsNullIfNotAlias(){
        $this->assertNull($this->domain->getAliasTarget());
    }

    public function testSetAliasTargetWillNotSetAliasTargetIfTargetDoesNotExist(){
        $this->domain->setAliasTarget($this->nonCreatedDomain);
        $this->assertFalse($this->domain->isAliasDomain());
    }

    public function testSetAliasTargetWillNotSetAliasTargetIfTargetIsNotInLibrary(){
        $this->nonCreatedDomain->create($this->mailPass);
        $this->domain->setAliasTarget($this->nonCreatedDomain);
        $this->assertFalse($this->domain->isAliasDomain());
    }

    public function testSetAliasTargetWillSetIfInLibraryAndExisting(){
        $this->nonCreatedDomain->create($this->mailPass);
        $this->domainLib->setDomainList(array($this->nonCreatedDomain->getDomainName()=>$this->nonCreatedDomain));
        $this->domain->setAliasTarget($this->nonCreatedDomain);
        $this->assertTrue($this->domain->isAliasDomain());
        $this->assertTrue($this->nonCreatedDomain === $this->domain->getAliasTarget());
    }

    public function testClearAliasTargetClears(){
        $this->nonCreatedDomain->create($this->mailPass);
        $this->domainLib->setDomainList(array($this->nonCreatedDomain->getDomainName()=>$this->nonCreatedDomain));
        $this->domain->setAliasTarget($this->nonCreatedDomain);
        $this->domain->clearAliasTarget();
        $this->assertFalse($this->domain->isAliasDomain());
    }

    public function testDeleteCallsObservers(){
        $ob = new StubObserverImpl();
        $this->domain->attachObserver($ob);
        $this->domain->delete($this->mailPass);
        $this->assertTrue($ob->hasBeenCalled());
        $this->assertEquals($ob->getLastCallType(), MailDomain::EVENT_DELETE);
        $this->assertEquals($ob->getLastCallSubject(), $this->domain);

    }

    public function testDetachObserverDetaches(){
        $ob = new StubObserverImpl();
        $this->domain->attachObserver($ob);
        $this->domain->detachObserver($ob);
        $this->domain->delete($this->mailPass);
        $this->assertFalse($ob->hasBeenCalled());
    }

    public function testDeleteAliasTargetsDisablesAlias(){
        $this->nonCreatedDomain->create($this->mailPass);
        $this->domainLib->setDomainList(array($this->nonCreatedDomain->getDomainName()=>$this->nonCreatedDomain));
        $this->domain->setAliasTarget($this->nonCreatedDomain);
        $this->nonCreatedDomain->delete($this->mailPass);
        $this->assertFalse($this->domain->isAliasDomain());
    }

    public function testAliasTargetIsSetPrDefault(){
        $this->domainLib->setDomainList(array(
            $this->domain->getDomainName()=>$this->domain,
            $this->domain2->getDomainName()=>$this->domain2));
        $this->assertTrue($this->domain2->isAliasDomain());
    }

    public function testChangeDeactivateIsPersistent(){
        $this->domain->deactivate();
        $d = $this->cloneDomain($this->domain);
        $this->assertEquals($this->domain->isActive(), $d->isActive());
    }


    public function testDeactivateUpdatesLastModified(){
        $t = $this->domain->lastModified();
        $this->domain->deactivate();
        $this->assertGreaterThan( $t, $this->domain->lastModified());

    }
    public function testActivateUpdatesLastModified(){
        $this->domain->deactivate();
        $t = $this->domain->lastModified();
        sleep(2);
        $this->domain->activate();
        $this->assertGreaterThan($t, $this->domain->lastModified());

    }

    public function testActivateActiveDoesNotChangeLastModified(){
        $t = $this->domain->lastModified();
        $this->domain->activate();
        $this->assertEquals($this->domain->lastModified(), $t);

    }

    public function testDeactivateNonActiveDoesNotChangeLastModified(){
        $this->domain->deactivate();
        $t = $this->domain->lastModified();
        sleep(2);

        $this->domain->deactivate();
        $this->assertEquals($this->domain->lastModified(), $t);

    }


    public function testChangeDescUpdatesLastModified(){
        $t = $this->domain->lastModified();
        $this->domain->setDescription("LOL");
        $this->assertGreaterThan($t, $this->domain->lastModified());

    }


    public function testChangeActivateIsPersistent(){
        $this->domain->deactivate();
        $this->domain->activate();
        $d = $this->cloneDomain($this->domain);
        $this->assertEquals($this->domain->isActive(), $d->isActive());
    }


    public function testSetDescIsPersistent(){
        $this->domain->setDescription("TEST");
        $d = $this->cloneDomain($this->domain);
        $this->assertEquals($this->domain->getDescription(), $d->getDescription());
    }

    public function testClearAliasIsPersistent(){
        $this->domain2->clearAliasTarget();
        $d = $this->cloneDomain($this->domain2);
        $this->assertFalse($d->isAliasDomain());
    }

    public function testSetAliasTargetIsPersistent(){
        $this->nonCreatedDomain->create($this->mailPass);
        $this->domainLib->setDomainList(array($this->nonCreatedDomain->getDomainName()=>$this->nonCreatedDomain));
        $this->domain->setAliasTarget($this->nonCreatedDomain);
        $d = $this->cloneDomain($this->domain);
        $this->assertTrue($d->isAliasDomain());
        $this->assertTrue($this->nonCreatedDomain == $d->getAliasTarget());
    }

    public function testGetAddressLibraryReturnsRightInstance(){
        $this->assertInstanceOf('ChristianBudde\cbweb\MailAddressLibraryImpl', $a = $this->domain->getAddressLibrary());
        $this->assertInstanceOf('ChristianBudde\cbweb\MailAddressLibrary', $a);
        $this->assertTrue($this->domain === $a->getDomain());
    }

    public function testGetAddressWillReuseInstance(){
        $this->assertTrue($this->domain->getAddressLibrary() === $this->domain->getAddressLibrary());
    }

    public function testGetAddressWillReuseInstanceOfDomain(){
        $this->assertTrue($this->domain->getAddressLibrary()->getDomain() === $this->domain);
    }

    public function testGetAddressWillReuseInstanceOfDomainLibrary(){
        $this->assertTrue($this->domain->getAddressLibrary()->getDomainLibrary() === $this->domainLib);
    }



    /**
     * @param MailDomainImpl $domain
     * @return MailDomainImpl
     */
    private function cloneDomain($domain)
    {
        return new MailDomainImpl($domain->getDomainName(), $this->databaseName, $this->db, $this->domainLib);
    }


} 