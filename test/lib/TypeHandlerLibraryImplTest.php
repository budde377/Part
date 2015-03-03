<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 8:36 PM
 */

namespace ChristianBudde\Part\test;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\BackendSingletonContainerImpl;
use ChristianBudde\Part\ConfigImpl;
use ChristianBudde\Part\controller\ajax\type_handler\BackendTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\TypeHandlerLibraryImpl;
use ChristianBudde\Part\log\LoggerImpl;
use ChristianBudde\Part\model\mail\AddressImpl;
use ChristianBudde\Part\model\mail\AddressLibraryImpl;
use ChristianBudde\Part\model\mail\MailboxImpl;
use ChristianBudde\Part\model\page\PageContentLibraryImpl;
use ChristianBudde\Part\model\site\SiteContentLibraryImpl;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\model\user\UserLibrary;
use ChristianBudde\Part\test\stub\StubMailDomainImpl;
use ChristianBudde\Part\test\stub\StubMailDomainLibraryImpl;
use ChristianBudde\Part\test\stub\StubPageContentImpl;
use ChristianBudde\Part\test\stub\StubPageImpl;
use ChristianBudde\Part\test\stub\StubPageOrderImpl;
use ChristianBudde\Part\test\stub\StubSiteContentImpl;
use ChristianBudde\Part\test\stub\StubSiteImpl;
use ChristianBudde\Part\test\stub\StubUpdaterImpl;
use ChristianBudde\Part\test\stub\StubUserImpl;
use ChristianBudde\Part\test\stub\StubUserLibraryImpl;
use ChristianBudde\Part\test\util\CustomDatabaseTestCase;
use ChristianBudde\Part\util\file\FileImpl;
use ChristianBudde\Part\util\file\FileLibraryImpl;
use ChristianBudde\Part\util\file\FolderImpl;
use ChristianBudde\Part\util\file\ImageFileImpl;


class TypeHandlerLibraryImplTest extends CustomDatabaseTestCase
{
    /** @var  BackendSingletonContainer */
    private $container;
    /** @var  UserLibrary */
    private $userLibrary;
    private $config;
    /** @var  BackendTypeHandlerImpl */
    private $typeHandler;
    /** @var  User */
    private $rootUser;
    /** @var  TypeHandlerLibraryImpl */
    private $typeHandlerLibrary;


    function __construct()
    {
        parent::__construct(dirname(__FILE__) . '/../mysqlXML/BackendAJAXTypeHandlerImplTest.xml');
    }


    protected function setUp()
    {

        parent::setUp();
        $host = self::$mysqlOptions->getHost();
        $username = self::$mysqlOptions->getUsername();
        $password = self::$mysqlOptions->getPassword();
        $database = self::$mysqlOptions->getDatabase();
        $mHost = self::$mailMySQLOptions->getHost();
        $mUsername = self::$mailMySQLOptions->getUsername();
        $mDatabase = self::$mailMySQLOptions->getDatabase();
        $tmpFolder = "/tmp/cbweb-test/" . uniqid();
        $folder = new FolderImpl($tmpFolder);
        $folder->create(true);
        $logFile = $tmpFolder . "logFile";
        $this->config = new ConfigImpl(simplexml_load_string("<?xml version='1.0' encoding='UTF-8'?>
<config xmlns='http://christianbud.de/site-config'>

    <siteInfo>
        <domain name='christianbud' extension='de'/>
        <owner name='Christian Budde Christensen' mail='christi@nbud.de' username='root'/>
    </siteInfo>
    <defaultPages>
        <page alias='' template='_login' id='login'>Login</page>
        <page alias='' template='_logout' id='logout'>Log ud</page>
        <page alias='' template='_500' id='_500'>Der er sket en fejl (500)</page>
    </defaultPages>
    <MySQLConnection>
        <host>$host</host>
        <database>$database</database>
        <username>$username</username>
        <password>$password</password>
    </MySQLConnection>
        <MailMySQLConnection>
        <host>$mHost</host>
        <database>$mDatabase</database>
        <username>$mUsername</username>
    </MailMySQLConnection>
    <enableUpdater>true</enableUpdater>
    <debugMode>false</debugMode>
    <tmpFolder path='$tmpFolder'/>
    <preScripts>
        <class >ChristianBudde\\Part\\util\\script\\UserLoginCheckPreScript</class>
        <class >ChristianBudde\\Part\\util\\script\\UserLoginUpdateCheckPreScript</class>
        <class >ChristianBudde\\Part\\util\\script\\RequireHTTPSPreScript</class>
    </preScripts>
    <log path='$logFile' />
</config>"), $tmpFolder);
        $this->container = new BackendSingletonContainerImpl($this->config);
        $this->typeHandler = new BackendTypeHandlerImpl($this->container);
        $this->rootUser = $this->container->getUserLibraryInstance()->getUser('root');
        $this->rootUser->getUserPrivileges()->addRootPrivileges();
        $this->userLibrary = $this->container->getUserLibraryInstance();
        $this->typeHandlerLibrary = new TypeHandlerLibraryImpl($this->container);
    }

    public function testTypeHandlerInstances()
    {

        $result = $this->typeHandlerLibrary->getFileLibraryTypeHandlerInstance(new FileLibraryImpl($this->container, new FolderImpl("/")));
        $this->assertNotNull($result);
        $result = $this->typeHandlerLibrary->getFileTypeHandlerInstance(new FileImpl(''));
        $this->assertNotNull($result);
        $result = $this->typeHandlerLibrary->getImageFileTypeHandlerInstance(new ImageFileImpl(''));
        $this->assertNotNull($result);

        $result = $this->typeHandlerLibrary->getUpdaterTypeHandlerInstance(new StubUpdaterImpl());
        $this->assertNotNull($result);
        $result = $this->typeHandlerLibrary->getLoggerTypeHandlerInstance(new LoggerImpl($this->container, ''));
        $this->assertNotNull($result);

        $result = $this->typeHandlerLibrary->getUserLibraryTypeHandlerInstance($this->userLibrary);
        $this->assertNotNull($result);
        $result = $this->typeHandlerLibrary->getUserTypeHandlerInstance(new StubUserImpl());
        $this->assertNotNull($result);

        $result = $this->typeHandlerLibrary->getSiteTypeHandlerInstance($site = new StubSiteImpl());
        $this->assertNotNull($result);

        $result = $this->typeHandlerLibrary->getPageTypeHandlerInstance($page = new StubPageImpl());
        $this->assertNotNull($result);
        $result = $this->typeHandlerLibrary->getPageOrderTypeHandlerInstance(new StubPageOrderImpl());
        $this->assertNotNull($result);

        $result = $this->typeHandlerLibrary->getMailDomainLibraryTypeHandlerInstance(new StubMailDomainLibraryImpl());
        $this->assertNotNull($result);
        $result = $this->typeHandlerLibrary->getMailDomainTypeHandlerInstance($domain = new StubMailDomainImpl(false, 'test.dk'));
        $this->assertNotNull($result);
        $result = $this->typeHandlerLibrary->getMailAddressLibraryTypeHandlerInstance($addressLibrary = new AddressLibraryImpl($this->container, $domain));
        $this->assertNotNull($result);
        $result = $this->typeHandlerLibrary->getMailAddressTypeHandlerInstance($address = new AddressImpl($this->container, 'test', $addressLibrary));
        $this->assertNotNull($result);
        $result = $this->typeHandlerLibrary->getMailboxTypeHandlerInstance(new MailboxImpl($this->container, $address));
        $this->assertNotNull($result);

        $result = $this->typeHandlerLibrary->getPageContentLibraryTypeHandlerInstance(new PageContentLibraryImpl($this->container, $page));
        $this->assertNotNull($result);
        $result = $this->typeHandlerLibrary->getPageContentTypeHandlerInstance(new StubPageContentImpl());
        $this->assertNotNull($result);

        $result = $this->typeHandlerLibrary->getSiteContentLibraryTypeHandlerInstance(new SiteContentLibraryImpl($this->container, $site));
        $this->assertNotNull($result);
        $result = $this->typeHandlerLibrary->getSiteContentTypeHandlerInstance(new StubSiteContentImpl());
        $this->assertNotNull($result);

    }


    public function testAssertRightInstance()
    {

        $result = $this->typeHandlerLibrary->getFileLibraryTypeHandlerInstance(new FileLibraryImpl($this->container, new FolderImpl("/")));
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\FileLibraryTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getFileTypeHandlerInstance(new FileImpl(''));
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\FileTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getImageFileTypeHandlerInstance(new ImageFileImpl(''));
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\ImageFileTypeHandlerImpl',$result);

        $result = $this->typeHandlerLibrary->getUpdaterTypeHandlerInstance(new StubUpdaterImpl());
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\UpdaterTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getLoggerTypeHandlerInstance(new LoggerImpl($this->container, ''));
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\LoggerTypeHandlerImpl',$result);

        $result = $this->typeHandlerLibrary->getUserLibraryTypeHandlerInstance($this->userLibrary);
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\UserLibraryTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getUserTypeHandlerInstance(new StubUserImpl());
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\UserTypeHandlerImpl',$result);

        $result = $this->typeHandlerLibrary->getSiteTypeHandlerInstance($site = new StubSiteImpl());
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\SiteTypeHandlerImpl',$result);

        $result = $this->typeHandlerLibrary->getPageTypeHandlerInstance($page = new StubPageImpl());
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\PageTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getPageOrderTypeHandlerInstance(new StubPageOrderImpl());
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\PageOrderTypeHandlerImpl',$result);

        $result = $this->typeHandlerLibrary->getMailDomainLibraryTypeHandlerInstance(new StubMailDomainLibraryImpl());
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\MailDomainLibraryTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getMailDomainTypeHandlerInstance($domain = new StubMailDomainImpl(false, 'test.dk'));
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\MailDomainTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getMailAddressLibraryTypeHandlerInstance($addressLibrary = new AddressLibraryImpl($this->container, $domain));
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\MailAddressLibraryTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getMailAddressTypeHandlerInstance($address = new AddressImpl($this->container, 'test', $addressLibrary));
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\MailAddressTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getMailboxTypeHandlerInstance(new MailboxImpl($this->container, $address));
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\MailboxTypeHandlerImpl',$result);

        $result = $this->typeHandlerLibrary->getPageContentLibraryTypeHandlerInstance(new PageContentLibraryImpl($this->container, $page));
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\PageContentLibraryTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getPageContentTypeHandlerInstance(new StubPageContentImpl());
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\PageContentTypeHandlerImpl',$result);

        $result = $this->typeHandlerLibrary->getSiteContentLibraryTypeHandlerInstance(new SiteContentLibraryImpl($this->container, $site));
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\SiteContentLibraryTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getSiteContentTypeHandlerInstance(new StubSiteContentImpl());
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\SiteContentTypeHandlerImpl',$result);

    }

    public function testNotReuseInstance()
    {

        $result = $this->typeHandlerLibrary->getFileLibraryTypeHandlerInstance(new FileLibraryImpl($this->container, new FolderImpl("/")));
        $result2 = $this->typeHandlerLibrary->getFileLibraryTypeHandlerInstance(new FileLibraryImpl($this->container, new FolderImpl("/")));
        $this->assertTrue($result !== $result2);
        $result = $this->typeHandlerLibrary->getFileTypeHandlerInstance(new FileImpl(''));
        $result2 = $this->typeHandlerLibrary->getFileTypeHandlerInstance(new FileImpl(''));
        $this->assertTrue($result !== $result2);
        $result = $this->typeHandlerLibrary->getImageFileTypeHandlerInstance(new ImageFileImpl(''));
        $result2 = $this->typeHandlerLibrary->getImageFileTypeHandlerInstance(new ImageFileImpl(''));
        $this->assertTrue($result !== $result2);

        $result = $this->typeHandlerLibrary->getUpdaterTypeHandlerInstance(new StubUpdaterImpl());
        $result2 = $this->typeHandlerLibrary->getUpdaterTypeHandlerInstance(new StubUpdaterImpl());
        $this->assertTrue($result !== $result2);
        $result = $this->typeHandlerLibrary->getLoggerTypeHandlerInstance(new LoggerImpl($this->container, ''));
        $result2 = $this->typeHandlerLibrary->getLoggerTypeHandlerInstance(new LoggerImpl($this->container, ''));
        $this->assertTrue($result !== $result2);

        $result = $this->typeHandlerLibrary->getUserLibraryTypeHandlerInstance($this->userLibrary);
        $result2 = $this->typeHandlerLibrary->getUserLibraryTypeHandlerInstance(new StubUserLibraryImpl());
        $this->assertTrue($result !== $result2);
        $result = $this->typeHandlerLibrary->getUserTypeHandlerInstance(new StubUserImpl());
        $result2 = $this->typeHandlerLibrary->getUserTypeHandlerInstance(new StubUserImpl());
        $this->assertTrue($result !== $result2);

        $result = $this->typeHandlerLibrary->getSiteTypeHandlerInstance($site = new StubSiteImpl());
        $result2 = $this->typeHandlerLibrary->getSiteTypeHandlerInstance($site = new StubSiteImpl());
        $this->assertTrue($result !== $result2);

        $result = $this->typeHandlerLibrary->getPageTypeHandlerInstance($page = new StubPageImpl());
        $result2 = $this->typeHandlerLibrary->getPageTypeHandlerInstance($page = new StubPageImpl());
        $this->assertTrue($result !== $result2);
        $result = $this->typeHandlerLibrary->getPageOrderTypeHandlerInstance(new StubPageOrderImpl());
        $result2 = $this->typeHandlerLibrary->getPageOrderTypeHandlerInstance(new StubPageOrderImpl());
        $this->assertTrue($result !== $result2);

        $result = $this->typeHandlerLibrary->getMailDomainLibraryTypeHandlerInstance(new StubMailDomainLibraryImpl());
        $result2 = $this->typeHandlerLibrary->getMailDomainLibraryTypeHandlerInstance(new StubMailDomainLibraryImpl());
        $this->assertTrue($result !== $result2);
        $result = $this->typeHandlerLibrary->getMailDomainTypeHandlerInstance($domain = new StubMailDomainImpl(false, 'test.dk'));
        $result2 = $this->typeHandlerLibrary->getMailDomainTypeHandlerInstance($domain = new StubMailDomainImpl(false, 'test.dk'));
        $this->assertTrue($result !== $result2);
        $result = $this->typeHandlerLibrary->getMailAddressLibraryTypeHandlerInstance($addressLibrary = new AddressLibraryImpl($this->container, $domain));
        $result2 = $this->typeHandlerLibrary->getMailAddressLibraryTypeHandlerInstance($addressLibrary = new AddressLibraryImpl($this->container, $domain));
        $this->assertTrue($result !== $result2);
        $result = $this->typeHandlerLibrary->getMailAddressTypeHandlerInstance($address = new AddressImpl($this->container, 'test', $addressLibrary));
        $result2 = $this->typeHandlerLibrary->getMailAddressTypeHandlerInstance($address = new AddressImpl($this->container, 'test', $addressLibrary));
        $this->assertTrue($result !== $result2);
        $result = $this->typeHandlerLibrary->getMailboxTypeHandlerInstance(new MailboxImpl($this->container, $address));
        $result2 = $this->typeHandlerLibrary->getMailboxTypeHandlerInstance(new MailboxImpl($this->container, $address));
        $this->assertTrue($result !== $result2);

        $result = $this->typeHandlerLibrary->getPageContentLibraryTypeHandlerInstance(new PageContentLibraryImpl($this->container, $page));
        $result2 = $this->typeHandlerLibrary->getPageContentLibraryTypeHandlerInstance(new PageContentLibraryImpl($this->container, $page));
        $this->assertTrue($result !== $result2);
        $result = $this->typeHandlerLibrary->getPageContentTypeHandlerInstance(new StubPageContentImpl());
        $result2 = $this->typeHandlerLibrary->getPageContentTypeHandlerInstance(new StubPageContentImpl());
        $this->assertTrue($result !== $result2);

        $result = $this->typeHandlerLibrary->getSiteContentLibraryTypeHandlerInstance(new SiteContentLibraryImpl($this->container, $site));
        $result2 = $this->typeHandlerLibrary->getSiteContentLibraryTypeHandlerInstance(new SiteContentLibraryImpl($this->container, $site));
        $this->assertTrue($result !== $result2);
        $result = $this->typeHandlerLibrary->getSiteContentTypeHandlerInstance(new StubSiteContentImpl());
        $result2 = $this->typeHandlerLibrary->getSiteContentTypeHandlerInstance(new StubSiteContentImpl());
        $this->assertTrue($result !== $result2);

    }

    public function testReuseInstance()
    {

        $result = $this->typeHandlerLibrary->getFileLibraryTypeHandlerInstance($instance = new FileLibraryImpl($this->container, new FolderImpl("/")));
        $result2 = $this->typeHandlerLibrary->getFileLibraryTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getFileTypeHandlerInstance($instance = new FileImpl(''));
        $result2 = $this->typeHandlerLibrary->getFileTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getImageFileTypeHandlerInstance($instance = new ImageFileImpl(''));
        $result2 = $this->typeHandlerLibrary->getImageFileTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);

        $result = $this->typeHandlerLibrary->getUpdaterTypeHandlerInstance($instance = new StubUpdaterImpl());
        $result2 = $this->typeHandlerLibrary->getUpdaterTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getLoggerTypeHandlerInstance($instance = new LoggerImpl($this->container, ''));
        $result2 = $this->typeHandlerLibrary->getLoggerTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);

        $result = $this->typeHandlerLibrary->getUserLibraryTypeHandlerInstance($instance = $this->userLibrary);
        $result2 = $this->typeHandlerLibrary->getUserLibraryTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getUserTypeHandlerInstance($instance = new StubUserImpl());
        $result2 = $this->typeHandlerLibrary->getUserTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);

        $result = $this->typeHandlerLibrary->getSiteTypeHandlerInstance($instance = $site = new StubSiteImpl());
        $result2 = $this->typeHandlerLibrary->getSiteTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);

        $result = $this->typeHandlerLibrary->getPageTypeHandlerInstance($instance = $page = new StubPageImpl());
        $result2 = $this->typeHandlerLibrary->getPageTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getPageOrderTypeHandlerInstance($instance = new StubPageOrderImpl());
        $result2 = $this->typeHandlerLibrary->getPageOrderTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);

        $result = $this->typeHandlerLibrary->getMailDomainLibraryTypeHandlerInstance($instance = new StubMailDomainLibraryImpl());
        $result2 = $this->typeHandlerLibrary->getMailDomainLibraryTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getMailDomainTypeHandlerInstance($instance = $domain = new StubMailDomainImpl(false, 'test.dk'));
        $result2 = $this->typeHandlerLibrary->getMailDomainTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getMailAddressLibraryTypeHandlerInstance($instance = $addressLibrary = new AddressLibraryImpl($this->container, $domain));
        $result2 = $this->typeHandlerLibrary->getMailAddressLibraryTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getMailAddressTypeHandlerInstance($instance = $address = new AddressImpl($this->container, 'test', $addressLibrary));
        $result2 = $this->typeHandlerLibrary->getMailAddressTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getMailboxTypeHandlerInstance($instance = new MailboxImpl($this->container, $address));
        $result2 = $this->typeHandlerLibrary->getMailboxTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);

        $result = $this->typeHandlerLibrary->getPageContentLibraryTypeHandlerInstance($instance = new PageContentLibraryImpl($this->container, $page));
        $result2 = $this->typeHandlerLibrary->getPageContentLibraryTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getPageContentTypeHandlerInstance($instance = new StubPageContentImpl());
        $result2 = $this->typeHandlerLibrary->getPageContentTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);

        $result = $this->typeHandlerLibrary->getSiteContentLibraryTypeHandlerInstance($instance = new SiteContentLibraryImpl($this->container, $site));
        $result2 = $this->typeHandlerLibrary->getSiteContentLibraryTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getSiteContentTypeHandlerInstance($instance = new StubSiteContentImpl());
        $result2 = $this->typeHandlerLibrary->getSiteContentTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);

    }

}