<?php
require_once dirname(__FILE__) . '/../_test/_stub/StubDBImpl.php';
require_once dirname(__FILE__) . '/../_test/_stub/StubSiteLibraryImpl.php';
require_once dirname(__FILE__) . '/../_class/UserPrivilegesLibraryImpl.php';
require_once dirname(__FILE__) . '/../_test/_stub/StubUserImpl.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 05/08/12
 * Time: 20:24
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
        $siteLibrary = new StubSiteLibraryImpl();
        $this->privilegesLibrary = new UserPrivilegesLibraryImpl($db,$siteLibrary);
        $this->user1 = new StubUserImpl();
        $this->user1->setUsername('someUsername');
        $this->user2 = new StubUserImpl();
        $this->user2->setUsername('someUsername');
    }

    public function testGetPrivilegeWillReturnPrivilege(){
        $ret = $this->privilegesLibrary->getPrivileges($this->user1);
        $this->assertInstanceOf('UserPrivileges',$ret);
    }

    public function testWillCachePrivilegesInstance(){
        $ret1 = $this->privilegesLibrary->getPrivileges($this->user1);
        $ret2 = $this->privilegesLibrary->getPrivileges($this->user1);

        $this->assertTrue($ret1 === $ret2,'Did not cache instance');
    }

    public function testWillCachePrivilegesInstanceNotOnUsername(){
        $ret1 = $this->privilegesLibrary->getPrivileges($this->user1);
        $ret2 = $this->privilegesLibrary->getPrivileges($this->user2);

        $this->assertTrue($ret1 !== $ret2,'Did not cache instance');
    }
}
