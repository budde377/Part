<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/14
 * Time: 11:11 PM
 */
namespace ChristianBudde\Part\model\site;

use ChristianBudde\Part\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\util\CustomDatabaseTestCase;
use ChristianBudde\Part\util\db\StubDBImpl;

class SiteImplTest extends CustomDatabaseTestCase
{


    private $db;
    /** @var  SiteImpl */
    private $site;
    /** @var  StubBackendSingletonContainerImpl */
    private $container;

    function __construct()
    {
        parent::__construct($GLOBALS['MYSQL_XML_DIR'] .  '/SiteImplTest.xml');
    }


    public function setUp()
    {
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->container = new StubBackendSingletonContainerImpl();
        $this->container->setDBInstance($this->db);
        $this->site = new SiteImpl($this->container);

    }

    public function testGetSiteContentReturnSameInstance()
    {
        $this->assertTrue($this->site->getContentLibrary() === $this->site->getContentLibrary());
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\ContentLibrary", $this->site->getContentLibrary());

    }

    public function testGetSiteContentReuseInstance()
    {
        $this->assertTrue($this->site->getContent("Test") === $this->site->getContent("Test"));
        $this->assertTrue($this->site->getContent("Test") === $this->site->getContentLibrary()->getContent("Test"));
    }

    public function testModifyWillChangeLastModified()
    {
        $t1 = $this->site->lastModified();
        $this->site->modify();
        $t2 = $this->site->lastModified();
        $this->assertGreaterThan($t1, $t2);
    }

    public function testVariablesWillReuseInstance()
    {
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\Variables", $this->site->getVariables());
        $this->assertTrue($this->site->getVariables() === $this->site->getVariables());
    }

    public function testModifyWillBePersistent()
    {
        $t1 = $this->site->lastModified();
        $this->site->modify();
        $site = new SiteImpl($this->container);
        $t2 = $site->lastModified();
        $this->assertGreaterThan($t1, $t2);

    }

    public function testGenerateTypeHandlerReusesInstance(){
        $this->assertEquals($this->site, $this->site->generateTypeHandler());
    }


}