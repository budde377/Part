<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/9/14
 * Time: 6:13 PM
 */

namespace ChristianBudde\Part\model\updater;

use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\model\user\UserImpl;
use ChristianBudde\Part\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\util\CustomDatabaseTestCase;
use ChristianBudde\Part\util\db\DB;
use ChristianBudde\Part\util\db\StubDBImpl;

class GitUpdaterImplTest extends CustomDatabaseTestCase{




    /** @var  DB */
    private $db;
    /** @var User */
    private $user;
    /** @var  GitUpdaterImpl */
    private $updater;
    /** @var  StubBackendSingletonContainerImpl */
    private $container;


    function __construct()
    {
        parent::__construct($GLOBALS['MYSQL_XML_DIR'] .  '/GitUpdaterImplTest.xml');
    }


    protected function setUp()
    {
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->container = new StubBackendSingletonContainerImpl();
        $this->container->setDBInstance($this->db);
        $this->user = new UserImpl($this->container, 'root');
        $this->updater = new GitUpdaterImpl($this->container, "/tmp/");
    }

    public function testUserCheckUpdateOnLoginIsTruePrDefault() {
        $this->assertTrue($this->updater->isCheckOnLoginAllowed($this->user));
    }

    public function testCanDisableCheckOnLogin(){
        $this->updater->disallowCheckOnLogin($this->user);
        $this->assertFalse($this->updater->isCheckOnLoginAllowed($this->user));
    }

    public function testCanEnableCheckOnLogin(){
        $this->updater->disallowCheckOnLogin($this->user);
        $this->updater->allowCheckOnLogin($this->user);
        $this->assertTrue($this->updater->isCheckOnLoginAllowed($this->user));
    }

    public function testEnableIsPersistent(){
        $this->updater->disallowCheckOnLogin($this->user);

        $user2 = new UserImpl($this->container, 'root');
        $this->assertFalse($this->updater->isCheckOnLoginAllowed($user2));
    }

    public function testGenerateTypeHandlerReusesInstance(){
        $this->assertEquals($this->updater, $this->updater->generateTypeHandler());
    }



} 