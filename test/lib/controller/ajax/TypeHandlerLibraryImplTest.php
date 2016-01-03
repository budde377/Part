<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 8:36 PM
 */

namespace ChristianBudde\Part\controller\ajax;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\BackendSingletonContainerImpl;
use ChristianBudde\Part\ConfigImpl;
use ChristianBudde\Part\controller\ajax\type_handler\BackendTypeHandlerImpl;
use ChristianBudde\Part\log\LoggerImpl;
use ChristianBudde\Part\model\page\PageContentLibraryImpl;
use ChristianBudde\Part\model\page\StubPageContentImpl;
use ChristianBudde\Part\model\page\StubPageImpl;
use ChristianBudde\Part\model\page\StubPageOrderImpl;
use ChristianBudde\Part\model\site\SiteContentLibraryImpl;
use ChristianBudde\Part\model\site\StubSiteContentImpl;
use ChristianBudde\Part\model\site\StubSiteImpl;
use ChristianBudde\Part\model\updater\StubUpdaterImpl;
use ChristianBudde\Part\model\user\StubUserImpl;
use ChristianBudde\Part\model\user\StubUserLibraryImpl;
use ChristianBudde\Part\model\user\StubUserPrivilegesImpl;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\model\user\UserLibrary;

use ChristianBudde\Part\util\CustomDatabaseTestCase;
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
        parent::__construct(dirname(__FILE__) . '/../../../mysqlXML/BackendAJAXTypeHandlerImplTest.xml');
    }


    protected function setUp()
    {

        parent::setUp();
        $host = self::$mysqlOptions->getHost();
        $username = self::$mysqlOptions->getUsername();
        $password = self::$mysqlOptions->getPassword();
        $database = self::$mysqlOptions->getDatabase();
        $tmpFolder = "/tmp/cbweb-test/" . uniqid();
        $folder = new FolderImpl($tmpFolder);
        $folder->create(true);
        $logFile = $tmpFolder . "logFile";
        $this->config = new ConfigImpl(simplexml_load_string(/** @lang XML */
            "<?xml version='1.0' encoding='UTF-8'?>
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
        $result = $this->typeHandlerLibrary->getUserPrivilegesTypeHandlerInstance(new StubUserPrivilegesImpl(true, true, true));
        $this->assertNotNull($result);

        $result = $this->typeHandlerLibrary->getSiteTypeHandlerInstance($site = new StubSiteImpl());
        $this->assertNotNull($result);

        $result = $this->typeHandlerLibrary->getPageTypeHandlerInstance($page = new StubPageImpl());
        $this->assertNotNull($result);
        $result = $this->typeHandlerLibrary->getPageOrderTypeHandlerInstance(new StubPageOrderImpl());
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
        $result = $this->typeHandlerLibrary->getUserPrivilegesTypeHandlerInstance(new StubUserPrivilegesImpl(true, true, true));
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\UserPrivilegesTypeHandlerImpl',$result);

        $result = $this->typeHandlerLibrary->getSiteTypeHandlerInstance($site = new StubSiteImpl());
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\SiteTypeHandlerImpl',$result);

        $result = $this->typeHandlerLibrary->getPageTypeHandlerInstance($page = new StubPageImpl());
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\PageTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getPageOrderTypeHandlerInstance(new StubPageOrderImpl());
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\PageOrderTypeHandlerImpl',$result);

        $result = $this->typeHandlerLibrary->getPageContentLibraryTypeHandlerInstance(new PageContentLibraryImpl($this->container, $page));
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\PageContentLibraryTypeHandlerImpl',$result);
        $result = $this->typeHandlerLibrary->getPageContentTypeHandlerInstance(new StubPageContentImpl());
        $this->assertInstanceOf('ChristianBudde\Part\controller\ajax\type_handler\PageContentTypeHandlerImpl',$result);

        $result = $this->typeHandlerLibrary->getSiteContentLibraryTypeHandlerInstance(new SiteContentLibraryImpl($this->container));
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
        $result = $this->typeHandlerLibrary->getUserPrivilegesTypeHandlerInstance(new StubUserPrivilegesImpl(true, true, true));
        $result2 = $this->typeHandlerLibrary->getUserPrivilegesTypeHandlerInstance(new StubUserPrivilegesImpl(true, true, true));
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

        $result = $this->typeHandlerLibrary->getPageContentLibraryTypeHandlerInstance(new PageContentLibraryImpl($this->container, $page));
        $result2 = $this->typeHandlerLibrary->getPageContentLibraryTypeHandlerInstance(new PageContentLibraryImpl($this->container, $page));
        $this->assertTrue($result !== $result2);
        $result = $this->typeHandlerLibrary->getPageContentTypeHandlerInstance(new StubPageContentImpl());
        $result2 = $this->typeHandlerLibrary->getPageContentTypeHandlerInstance(new StubPageContentImpl());
        $this->assertTrue($result !== $result2);

        $result = $this->typeHandlerLibrary->getSiteContentLibraryTypeHandlerInstance(new SiteContentLibraryImpl($this->container));
        $result2 = $this->typeHandlerLibrary->getSiteContentLibraryTypeHandlerInstance(new SiteContentLibraryImpl($this->container));
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
        $result = $this->typeHandlerLibrary->getUserPrivilegesTypeHandlerInstance($instance = new StubUserPrivilegesImpl(true, true, true));
        $result2 = $this->typeHandlerLibrary->getUserPrivilegesTypeHandlerInstance($instance);
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

        $result = $this->typeHandlerLibrary->getPageContentLibraryTypeHandlerInstance($instance = new PageContentLibraryImpl($this->container, $page));
        $result2 = $this->typeHandlerLibrary->getPageContentLibraryTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getPageContentTypeHandlerInstance($instance = new StubPageContentImpl());
        $result2 = $this->typeHandlerLibrary->getPageContentTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);

        $result = $this->typeHandlerLibrary->getSiteContentLibraryTypeHandlerInstance($instance = new SiteContentLibraryImpl($this->container));
        $result2 = $this->typeHandlerLibrary->getSiteContentLibraryTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);
        $result = $this->typeHandlerLibrary->getSiteContentTypeHandlerInstance($instance = new StubSiteContentImpl());
        $result2 = $this->typeHandlerLibrary->getSiteContentTypeHandlerInstance($instance);
        $this->assertTrue($result === $result2);

    }

}