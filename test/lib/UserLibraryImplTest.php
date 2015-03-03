<?php
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\json\UserLibraryObjectImpl;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\model\user\UserImpl;
use ChristianBudde\Part\model\user\UserLibraryImpl;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubConfigImpl;
use ChristianBudde\Part\test\stub\StubDBImpl;
use ChristianBudde\Part\test\stub\StubTypeHandlerLibraryImpl;
use ChristianBudde\Part\test\util\CustomDatabaseTestCase;

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

    /** @var  BackendSingletonContainer */
    private $container;
    /** @var  StubTypeHandlerLibraryImpl */
    private $typeHandlerLibrary;

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
        $this->container = new StubBackendSingletonContainerImpl();
        $this->container->setTypeHandlerLibraryInstance($this->typeHandlerLibrary = new StubTypeHandlerLibraryImpl());

        $this->container->setDBInstance($this->db);
        $this->container->setConfigInstance(new StubConfigImpl());
        $this->library = new UserLibraryImpl($this->container);
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

    public function testCanCreateWithoutParent()
    {
        $user = $this->library->createUser($username = 'user4', 'password', $mail = 'test@test.dk');
        $this->assertTrue($user->exists());
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($mail, $user->getMail());
        $this->assertNull($user->getParent());
    }


    public function testCreateUserWillCreateUser()
    {
        $ret = $this->library->createUser('user4', 'password', 'user3@test.dk', $this->library->getUser('user1'));
        $this->assertInstanceOf('ChristianBudde\Part\model\user\User', $ret, 'Did not return user');
        $this->assertTrue($ret->exists(), 'User does not exist');
    }

    public function testLoggingInAfterCreatingIsOk()
    {
        $user = $this->library->createUser('user4', $password = 'password', 'user3@test.dk', $this->library->getUser('user1'));
        $user->login($password);
        $this->assertEquals($user, $this->library->getUserLoggedIn());
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
        /** @var $user \ChristianBudde\Part\model\user\User */
        $user = $list[1];
        $ret = $this->library->deleteUser($user);
        $this->assertTrue($ret, 'Delete did not return true');
        $this->assertFalse($user->exists(), 'User was not deleted');
        $list = $this->library->listUsers();
        $this->assertFalse($this->userInList($list, $user), 'User was not removed from list');

    }

    public function testDeleteUserOnUserNILWillReturnFalse()
    {
        $user = new UserImpl($this->container, 'user1');
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
        /** @var $user \ChristianBudde\Part\model\user\User */
        $user = $list[1];
        $ret = $user->setPassword($password);
        $this->assertTrue($ret, 'Could not set password');
        $ret = $user->login($password);
        $this->assertTrue($ret, 'Could not log in');

        $loggedIn = $this->library->getUserLoggedIn();
        $this->assertTrue($loggedIn === $user, 'Did not return logged in user');
    }

    public function testGetLoggedInWillReturnLoggedInWithUserLoggedInNotInInstance()
    {
        $user = new UserImpl($this->container, 'user1');
        $user->setPassword($password = "SomePass");
        $this->assertTrue($user->login($password));
        $this->assertTrue($user->exists());
        $this->assertTrue($user->isLoggedIn());

        $this->library = new UserLibraryImpl($this->container);
        $loggedIn = $this->library->getUserLoggedIn();
        $this->assertTrue($loggedIn->getUsername() == $user->getUsername(), 'Did not return logged in user');
    }


    public function testGetLoggedInWillReturnNullWithUserLoggedOut()
    {
        $password = 'somePassword';
        $list = $this->library->listUsers();
        /** @var $user \ChristianBudde\Part\model\user\User */
        $user = $list[1];
        $user->setPassword($password);
        $user->login($password);
        $this->assertTrue($user === $this->library->getUserLoggedIn());
        $user->logout();
        $this->assertNull($this->library->getUserLoggedIn(), 'Was not null');
    }

    public function testDeleteOnObjectWillReflectInList()
    {
        $list = $this->library->listUsers();
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
        $this->assertInstanceOf('ChristianBudde\Part\model\user\User', $user, 'Did not return instance of User');
        $this->assertEquals('user2', $user->getUsername(), 'Usernames did not match');
    }


    public function testGetParentWillReturnParentAsUserInstance()
    {
        $user = $this->library->getUser('user2');
        $user = $this->library->getParent($user);
        $this->assertInstanceOf('ChristianBudde\Part\model\user\User', $user);
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
        $this->assertInstanceOf('ChristianBudde\Part\model\user\User', $user);
        $this->assertEquals('user11', $user->getUsername(), 'User names did not match');
    }

    public function testGetChildrenWillReturnChildren()
    {
        $parent = $this->library->getUser('user1');
        $ret = $this->library->getChildren($parent);
        $this->assertTrue(is_array($ret), 'Did not return array');
        $this->assertEquals(2, count($ret), 'Did not return array of right size');
        $this->assertInstanceOf('ChristianBudde\Part\model\user\User', $u1 = $ret[0]);
        $this->assertInstanceOf('ChristianBudde\Part\model\user\User', $u2 = $ret[1]);
        /** @var User $u1 */
        /** @var \ChristianBudde\Part\model\user\User $u2 */
        $this->assertTrue($u1->getUsername() == 'user2' || $u1->getUsername() == 'user3');
        $this->assertTrue($u2->getUsername() == 'user3' || $u2->getUsername() == 'user2');

    }

    public function testDeleteParentWillChangeParentOfChildren()
    {
        $list = $this->library->listUsers();
        $user1 = $list[0];
        $user2 = $list[1];
        $user3 = $list[2];

        $ret = $this->library->deleteUser($user2);
        $this->assertTrue($ret, 'Did not return true');
        $this->assertEquals($user1->getUsername(), $user3->getParent(), 'Parent did not match');
    }


    public function testUserSessionTokenIsNullWithNoUser(){
        $this->assertNull($this->library->getUserSessionToken());
    }

    public function testUserSessionTokenIsNotNullWithUserLoggedIn(){

        $list = $this->library->listUsers();
        $user = $list[0];
        $user->setPassword($password = 'somePassword');
        $this->assertTrue($user->login($password));
        $this->assertNotNull($this->library->getUserSessionToken());
    }
    public function testUserSessionsTokenWillNotReuseToken(){

        $list = $this->library->listUsers();
        $user = $list[0];
        $user->setPassword($password = 'somePassword');
        $this->assertTrue($user->login($password));
        $token = $this->library->getUserSessionToken();
        $user->logout();
        sleep(1);
        $this->assertTrue($user->login($password));
        $this->assertNotNull($token2 = $this->library->getUserSessionToken());
        $this->assertNotEquals($token, $token2);
    }


    public function testChangeUsernameWillNotChangeToken(){
        $user = $this->library->listUsers()[0];
        $user->setPassword($password = "SomePassword");
        $this->assertTrue($user->login($password));
        $token = $this->library->getUserSessionToken();
        $user->setUsername("bob");
        $token2 = $this->library->getUserSessionToken();
        $this->assertEquals($token, $token2);
    }

    public function testUserSessionTokenIsNullAfterLogout(){

        $list = $this->library->listUsers();
        $user = $list[0];
        $user->setPassword($password = 'somePassword');
        $this->assertTrue($user->login($password));
        $user->logout();
        $this->assertNull($this->library->getUserSessionToken());
    }

    public function testVerifyTokenIsTrueBeforeLogin(){
        $this->assertTrue($this->library->verifyUserSessionToken("Arb. string"));
    }

    public function testVerifyTokenIsTrueWithRightTokenOnLogin(){
        $list = $this->library->listUsers();
        $user = $list[0];
        $user->setPassword($password = 'somePassword');
        $this->assertTrue($user->login($password));
        $token = $this->library->getUserSessionToken();
        $this->assertTrue($this->library->verifyUserSessionToken($token));
        $this->assertFalse($this->library->verifyUserSessionToken($token."test"));
    }

    public function testVerifyTokenIsTrueAfterLogout(){

        $list = $this->library->listUsers();
        $user = $list[0];
        $user->setPassword($password = 'somePassword');
        $this->assertTrue($user->login($password));
        $user->logout();
        $this->assertTrue($this->library->verifyUserSessionToken("Arb. string"));
    }

    public function testLibraryIsJSONObjectSerializable()
    {
        $this->assertEquals(new UserLibraryObjectImpl($this->library), $this->library->jsonObjectSerialize());
    }

    public function testGenerateTypeHandlerReusesInstance(){
        $this->assertEquals($this->library, $this->library->generateTypeHandler());
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
