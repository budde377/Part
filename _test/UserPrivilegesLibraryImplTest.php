<?php
require_once dirname(__FILE__).'/_stub/StubDBImpl.php';
require_once dirname(__FILE__).'/_stub/StubUserImpl.php';
require_once dirname(__FILE__).'/../_class/UserPrivilegesLibraryImpl.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 19/01/13
 * Time: 13:22
 */
class UserPrivilegesLibraryImplTest extends PHPUnit_Framework_TestCase
{
    /** @var $privilegesLibrary UserPrivilegesLibraryImpl */
    private $privilegesLibrary;
    /** @var $user1 User */
    private $user1;
    /** @var $user2 User */
    private $user2;

    public function setUp(){
        $db = new StubDBImpl();
        $this->privilegesLibrary = new UserPrivilegesLibraryImpl($db);
        $this->user1 = new StubUserImpl();
        $this->user1->setUsername('someUsername');
        $this->user2 = new StubUserImpl();
        $this->user2->setUsername('someUsername');
    }

    public function testGetPrivilegeWillReturnPrivilege(){
        $ret = $this->privilegesLibrary->getUserPrivileges($this->user1);
        $this->assertInstanceOf('UserPrivileges',$ret);
    }

    public function testWillCachePrivilegesInstance(){
        $ret1 = $this->privilegesLibrary->getUserPrivileges($this->user1);
        $ret2 = $this->privilegesLibrary->getUserPrivileges($this->user1);

        $this->assertTrue($ret1 === $ret2,'Did not cache instance');
    }

    public function testWillCachePrivilegesInstanceNotOnUsername(){
        $ret1 = $this->privilegesLibrary->getUserPrivileges($this->user1);
        $ret2 = $this->privilegesLibrary->getUserPrivileges($this->user2);

        $this->assertTrue($ret1 !== $ret2,'Did not cache instance');
    }
}
