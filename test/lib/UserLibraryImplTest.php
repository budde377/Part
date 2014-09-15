<?php
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\model\user\UserLibraryImpl;
use ChristianBudde\cbweb\model\user\UserImpl;
use ChristianBudde\cbweb\model\user\User;
use ChristianBudde\cbweb\controller\json\UserLibraryJSONObjectImpl;
use ChristianBudde\cbweb\test\stub\StubDBImpl;
use ChristianBudde\cbweb\test\util\CustomDatabaseTestCase;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 23/07/12
 * Time: 16:49
 */
class UserLibraryImplTest extends CustomDatabaseTestCase
{

    /** @var $db StubDBImpl */
    private $db;
    /** @var $user UserLibraryImpl */
    private $library;

    function __construct($dataset = null)
    {
        parent::__construct(dirname(__FILE__) . '/../mysqlXML/UserLibraryImplTest.xml');
    }


    public function setUp()
    {
        parent::setUp();
        @session_destroy();
        @session_start();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->library = new UserLibraryImpl($this->db);
    }


    public function testListUsersWillListUsers()
    {
        $ret = $this->library->listUsers();
        $this->assertTrue(is_array($ret), 'Did not return array');
        $this->assertEquals(3, count($ret), 'Did not return array of right length');
    }

    public function testListUsersWillMatchResultOfIterator()
    {
        $list = $this->library->listUsers();
        foreach ($this->library as $key => $users) {
            $this->assertTrue(isset($list[$key]), 'Key was not found');
            $this->assertTrue($list[$key] === $users, 'Users did not match');
            unset($list[$key]);
        }
        $this->assertEquals(0, count($list), 'List was not covered');

    }

    public function testListUsersWillMatchResultAfterDeleteChange()
    {
        $user = $this->library->getUser('user2');
        $this->assertTrue($this->library->deleteUser($user));
        $list = $this->library->listUsers();
        foreach ($this->library as $key => $users) {
            $this->assertTrue(isset($list[$key]), 'Key was not found');
            $this->assertTrue($list[$key] === $users, 'Users did not match');
            unset($list[$key]);
        }
        $this->assertEquals(0, count($list), 'List was not covered');


    }

    public function testListUsersWillMatchResultAfterRemoteDeleteChange()
    {
        $user = $this->library->getUser('user2');
        $user->delete();
        $list = $this->library->listUsers();
        foreach ($this->library as $key => $users) {
            $this->assertTrue(isset($list[$key]), 'Key was not found');
            $this->assertTrue($list[$key] === $users, 'Users did not match');
            unset($list[$key]);
        }
        $this->assertEquals(0, count($list), 'List was not covered');


    }

    public function testListUsersWillMatchResultAfterCreateChange()
    {
        $user = $this->library->getUser('user2');
        $this->library->createUser('user4', 'password', 'test@test.dk', $user);
        $list = $this->library->listUsers();
        foreach ($this->library as $key => $users) {
            $this->assertTrue(isset($list[$key]), 'Key was not found');
            $this->assertTrue($list[$key] === $users, 'Users did not match');
            unset($list[$key]);
        }
        $this->assertEquals(0, count($list), 'List was not covered');
    }


    public function testCreateUserWillCreateUser()
    {
        $ret = $this->library->createUser('user4', 'password', 'user3@test.dk', $this->library->getUser('user1'));
        $this->assertInstanceOf('ChristianBudde\cbweb\model\user\User', $ret, 'Did not return user');
        $this->assertTrue($ret->exists(), 'User does not exist');
    }

    public function testCreateExistingUserWillReturnFalse()
    {
        $ret = $this->library->createUser('user1', 'somePass', 'test@test.dk', $this->library->getUser('user1'));
        $this->assertFalse($ret);
    }

    public function testCreatedUserWillBeInUserList()
    {
        $user = $this->library->createUser('user4', 'password', 'user3@test.dk', $this->library->getUser('user1'));
        $ret = $this->library->listUsers();
        $this->assertEquals(4, count($ret));

        $this->assertTrue($this->userInList($ret, $user), 'User was not in list');
    }

    public function testCreateUserWillSetParent()
    {
        $user1 = $this->library->getUser('user1');
        $user2 = $this->library->createUser('user4', 'password', 'user3@test.dk', $this->library->getUser('user1'));
        $this->assertEquals($user1->getUsername(), $user2->getParent(), 'Parent did not match');
    }

    public function testDeleteUserWillDeleteUser()
    {
        $list = $this->library->listUsers();
        /** @var $user \ChristianBudde\cbweb\model\user\User */
        $user = $list[1];
        $ret = $this->library->deleteUser($user);
        $this->assertTrue($ret, 'Delete did not return true');
        $this->assertFalse($user->exists(), 'User was not deleted');
        $list = $this->library->listUsers();
        $this->assertFalse($this->userInList($list, $user), 'User was not removed from list');

    }

    public function testDeleteUserOnUserNILWillReturnFalse()
    {
        $user = new UserImpl('user1', $this->db);
        $this->assertTrue($user->exists(), 'User did not exist');
        $ret = $this->library->deleteUser($user);
        $this->assertFalse($ret);
        $this->assertTrue($user->exists(), 'User was deleted');
    }

    public function testUserDeleteWillPreserveListNum()
    {
        $list = $this->library->listUsers();
        /** @var $user User */
        $user = $list[1];

        $ret = $this->library->deleteUser($user);
        $this->assertTrue($ret, 'Delete was not successful');

        $list = $this->library->listUsers();
        $this->assertArrayHasKey(0, $list, 'Did not preserve list num');
    }


    public function testGetLoggedInWillReturnNullWithNoLoggedIn()
    {
        $ret = $this->library->getUserLoggedIn();
        $this->assertNull($ret, 'Did not return null');
    }

    public function testGetLoggedInWillReturnLoggedInWithUserLoggedIn()
    {
        $password = 'somePassword';
        $list = $this->library->listUsers();
        /** @var $user \ChristianBudde\cbweb\model\user\User */
        $user = $list[1];
        $ret = $user->setPassword($password);
        $this->assertTrue($ret, 'Could not set password');
        $ret = $user->login($password);
        $this->assertTrue($ret, 'Could not log in');

        $loggedIn = $this->library->getUserLoggedIn();
        $this->assertTrue($loggedIn === $user, 'Did not return logged in user');
    }

    /**
     * public function testGetLoggedInWillReturnLoggedInWithLoginDoneOnInstanceNIL(){
     * $password = 'somePassword';
     * $user = new UserImpl('user1',$this->db);
     * $ret = $user->setPassword($password);
     * $this->assertTrue($ret,'Could not set password');
     * $ret = $user->login($password);
     * $this->assertTrue($ret,'Could not log in');
     *
     * $loggedIn = $this->library->getUserLoggedIn();
     * $this->assertEquals($user->getUsername(),$loggedIn->getUsername(),'Usernames did not match');
     * }
     **/
    public function testGetLoggedInWillReturnNullWithUserLoggedOut()
    {
        $password = 'somePassword';
        $user = new UserImpl('user1', $this->db);
        $ret = $user->setPassword($password);
        $this->assertTrue($ret, 'Could not set password');
        $ret = $user->login($password);
        $this->assertTrue($ret, 'Could not log in');
        $ret = $user->logout();
        $this->assertTrue($ret, 'Did not log out');

        $loggedIn = $this->library->getUserLoggedIn();
        $this->assertNull($loggedIn, 'Was not null');
    }

    public function testDeleteOnObjectWillReflectInList()
    {
        $list = $this->library->listUsers();
        /** @var $user \ChristianBudde\cbweb\model\user\User */
        $user = $list[2];
        $user->delete();
        $list = $this->library->listUsers();
        $this->assertFalse($this->userInList($list, $user), 'Did not delete in library');
    }

    public function testGetUserWillReturnNullIfUserNIL()
    {
        $user = $this->library->getUser('NonExistingUser');
        $this->assertNull($user);

    }

    public function testGetUSerWillReturnUserIfNotNIL()
    {
        $user = $this->library->getUser('user2');
        $this->assertInstanceOf('ChristianBudde\cbweb\model\user\User', $user, 'Did not return instance of User');
        $this->assertEquals('user2', $user->getUsername(), 'Usernames did not match');
    }


    public function testGetParentWillReturnParentAsUserInstance()
    {
        $user = $this->library->getUser('user2');
        $user = $this->library->getParent($user);
        $this->assertInstanceOf('ChristianBudde\cbweb\model\user\User', $user);
        $this->assertEquals('user1', $user->getUsername());
        $this->assertFalse($this->library->deleteUser($user), 'Could not delete user');
    }

    public function testGetParentWillReturnNullIfNoParent()
    {
        $user = $this->library->getUser('user1');
        $this->assertNull($this->library->getParent($user));
    }

    public function testChangeUsernameWillStillWork()
    {
        $user = $this->library->getUser('user1');
        $user->setUsername('user11');
        $user = $this->library->getUser('user11');
        $this->assertInstanceOf('ChristianBudde\cbweb\model\user\User', $user);
        $this->assertEquals('user11', $user->getUsername(), 'User names did not match');
    }

    public function testGetChildrenWillReturnChildren()
    {
        $parent = $this->library->getUser('user1');
        $ret = $this->library->getChildren($parent);
        $this->assertTrue(is_array($ret), 'Did not return array');
        $this->assertEquals(2, count($ret), 'Did not return array of right size');
        $this->assertInstanceOf('ChristianBudde\cbweb\model\user\User', $u1 = $ret[0]);
        $this->assertInstanceOf('ChristianBudde\cbweb\model\user\User', $u2 = $ret[1]);
        /** @var User $u1 */
        /** @var \ChristianBudde\cbweb\model\user\User $u2 */
        $this->assertTrue($u1->getUsername() == 'user2' || $u1->getUsername() == 'user3');
        $this->assertTrue($u2->getUsername() == 'user3' || $u2->getUsername() == 'user2');

    }

    public function testDeleteParentWillChangeParentOfChildren()
    {
        $list = $this->library->listUsers();
        /** @var $user1 \ChristianBudde\cbweb\model\user\UserImpl */
        $user1 = $list[0];
        /** @var $user2 \ChristianBudde\cbweb\model\user\UserImpl */
        $user2 = $list[1];
        /** @var $user3 \ChristianBudde\cbweb\model\user\UserImpl */
        $user3 = $list[2];

        $ret = $this->library->deleteUser($user2);
        $this->assertTrue($ret, 'Did not return true');
        $this->assertEquals($user1->getUsername(), $user3->getParent(), 'Parent did not match');
    }

    public function testLibraryIsJSONObjectSerializable()
    {
        $this->assertEquals(new UserLibraryJSONObjectImpl($this->library), $this->library->jsonObjectSerialize());
    }

    private function userInList($ret, User $user)
    {
        $success = false;
        foreach ($ret as $u) {
            $success = ($u === $user || $success == true);
        }
        return $success;
    }


}