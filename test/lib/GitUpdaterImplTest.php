<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/9/14
 * Time: 6:13 PM
 */

namespace ChristianBudde\cbweb\test;


use ChristianBudde\cbweb\model\updater\GitUpdaterImpl;
use ChristianBudde\cbweb\model\user\User;
use ChristianBudde\cbweb\model\user\UserImpl;
use ChristianBudde\cbweb\test\stub\StubDBImpl;
use ChristianBudde\cbweb\test\stub\StubSiteImpl;
use ChristianBudde\cbweb\test\util\CustomDatabaseTestCase;
use ChristianBudde\cbweb\util\db\DB;

class GitUpdaterImplTest extends CustomDatabaseTestCase{




    /** @var  DB */
    private $db;
    /** @var User */
    private $user;
    /** @var  GitUpdaterImpl */
    private $updater;


    function __construct()
    {
        parent::__construct(dirname(__FILE__) . '/../mysqlXML/GitUpdaterImplTest.xml');
    }


    protected function setUp()
    {
        parent::setUp();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->user = new UserImpl('root', $this->db);
        $this->updater = new GitUpdaterImpl("/tmp/", new StubSiteImpl());
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
        $user2 = new UserImpl('root', $this->db);
        $this->assertFalse($this->updater->isCheckOnLoginAllowed($user2));
    }


} 