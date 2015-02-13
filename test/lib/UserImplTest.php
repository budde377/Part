<?php
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\model\user\UserImpl;
use ChristianBudde\Part\test\stub\StubDBImpl;
use ChristianBudde\Part\test\stub\StubObserverImpl;
use ChristianBudde\Part\test\util\CustomDatabaseTestCase;
use ChristianBudde\Part\test\util\TruncateOperation;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Extensions_Database_Operation_Composite;
use PHPUnit_Extensions_Database_Operation_Factory;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/07/12
 * Time: 14:35
 */
class UserImplTest extends CustomDatabaseTestCase
{
    /** @var $db StubDBImpl */
    private $db;
    /** @var $user \ChristianBudde\Part\model\user\UserImpl */
    private $user;
    /** @var $user \ChristianBudde\Part\model\user\UserImpl */
    private $user2;


    function __construct($dataset = null)
    {
        parent::__construct(dirname(__FILE__) . '/../mysqlXML/UserImplTest.xml');
    }


    /*
     * Assumes that the UserImpl uses SESSION to handle login
     */

    public function setUp()
    {
        parent::setUp();
        @session_destroy();
        @session_start();
        $this->db = new StubDBImpl();
        $this->db->setConnection(self::$pdo);
        $this->user = new UserImpl('someUser', $this->db);
        $this->user2 = new UserImpl('root', $this->db);
    }


    public function testGetUsernameWillMatchUsernameOfConstructor()
    {
        $ret = $this->user->getUsername();
        $this->assertEquals('someUser', $ret);
    }


    public function testVerifyPasswordWillReturnTrueOnRightPassword()
    {
        $password = 'someValidPassword';
        $ret = $this->user->setPassword($password);
        $this->assertTrue($ret, 'Did not return true');
        $ret = $this->user->verifyLogin($password);
        $this->assertTrue($ret, 'Did not return true');

    }

    public function testVerifyPasswordWillReturnFalseOnWrongPassword()
    {
        $password = 'someValidPassword';
        $ret = $this->user->setPassword($password);
        $this->assertTrue($ret, 'Did not return true');
        $ret = $this->user->verifyLogin('someOtherPassword');
        $this->assertFalse($ret, 'Did return true');

    }

    public function testSettersWillSet()
    {
        $mail = 'test@TEST.dk';
        $username = 'someOtherUsername';
        $password = 'somePassword';

        $ret = $this->user->setMail($mail);
        $this->assertTrue($ret, 'Did not return true');
        $this->assertEquals($mail, $this->user->getMail(), 'Values did not match');

        $ret = $this->user->setPassword($password);
        $this->assertTrue($ret, 'Did not return true');
        $this->assertTrue($this->user->verifyLogin($password), 'Password was not changed');

        $ret = $this->user->setUsername($username);
        $this->assertTrue($ret, 'Did not return true');
        $this->assertEquals($username, $this->user->getUsername(), 'Values did not match');


    }

    public function testSettersWillNotChangeId()
    {
        $id = $this->user->getUniqueId();
        $this->user->setMail("test@test.dk");
        $this->assertEquals($id, $this->user->getUniqueId());
        $this->user->setPassword("asd");
        $this->assertEquals($id, $this->user->getUniqueId());
        $this->user->setUsername("asdasdasdasd");
        $this->assertEquals($id, $this->user->getUniqueId());
    }

    public function testCreateUserWillCreateDifferentIDs()
    {
        $user1 = new UserImpl('test1', $this->db);
        $user2 = new UserImpl('test2', $this->db);
        $this->assertNotEquals($user1->getUniqueId(), $user2->getUniqueId());
    }

    public function testSetMailMustBeValidMail()
    {
        $mail = 'invalidMail';
        $ret = $this->user->setMail($mail);
        $this->assertFalse($ret, 'Did not return false');
        $this->assertNotEquals($mail, $this->user->getMail(), 'Mail was changed');
    }

    public function testSetPasswordMustBeOfSomeLength()
    {
        $password = null;
        $ret = $this->user->setPassword($password);
        $this->assertFalse($ret, 'Did not return false');
        $password = '';
        $ret = $this->user->setPassword($password);
        $this->assertFalse($ret, 'Did not return false');
    }

    public function testSetUsernameToCurrentWillReturnTrue()
    {
        $user = new UserImpl('root', $this->db);
        $this->assertTrue($user->exists());
        $this->assertTrue($user->setUsername($user->getUsername()));
    }

    public function testSetUsernameNonUniqueWillReturnFalse()
    {
        $password = 'somePassword';
        $this->user->setMail('test@test.dk');
        $this->user->setPassword($password);
        $ret = $this->user->setUsername('root');
        $this->assertFalse($ret, 'Did not return false');
    }

    /*    public function testSetUsernameInvalidPasswordWillReturnFalse()
        {
            $password = 'somePassword';
            $this->user->setMail('test@test.dk');
            $this->user->setPassword($password);
            $ret = $this->user->setUsername('someOtherUsername');
            $this->assertFalse($ret, 'Did not return false');
        }*/


    public function testExistsWillReturnFalseIfNotExists()
    {
        $ret = $this->user->exists();
        $this->assertFalse($ret, 'Did not return false');
    }

    public function testExistsWillReturnTrueIfExists()
    {
        $user = new UserImpl('root', $this->db);
        $ret = $user->exists();
        $this->assertTrue($ret, 'Did not return true');
    }

    public function testCreateWillCreateUser()
    {
        $this->user->setMail('test@test.dk');
        $this->user->setPassword('somePassword');

        $ret = $this->user->exists();
        $this->assertFalse($ret, 'User did exists');

        $ret = $this->user->create();
        $this->assertTrue($ret, 'Did not return true');

        $ret = $this->user->exists();
        $this->assertTrue($ret, 'User was not created');

    }

    public function testCreateExistingUserWillReturnFalse()
    {
        $user = new UserImpl('root', $this->db);
        $ret = $user->create();
        $this->assertFalse($ret, 'Did not return false');
    }

    public function testCreateRequireThatVariablesAreSet()
    {
        $ret = $this->user->create();
        $this->assertFalse($ret, 'Did not return false');

        $ret = $this->user->exists();
        $this->assertFalse($ret, 'User was created');
    }


    public function testChangesOnExistingWillBePersistent()
    {
        $mail = 'test@TEST.dk';
        $username = 'someOtherUsername';
        $password = 'somePassword';

        $user = new UserImpl('root', $this->db);

        $ret = $user->exists();
        $this->assertTrue($ret, 'User did not exists');

        $ret = $user->setMail($mail);
        $this->assertTrue($ret, 'Did not return true');

        $ret = $user->setPassword($password);
        $this->assertTrue($ret, 'Did not return true');

        $ret = $user->setUsername($username);
        $this->assertTrue($ret, 'Did not return true');

        $user = new UserImpl('someOtherUsername', $this->db);

        $ret = $user->exists();
        $this->assertTrue($ret, 'Did not return true');
        $this->assertEquals($mail, $user->getMail(), 'Mail was not changed');
        $this->assertEquals($username, $user->getUsername(), 'Username was not changed');
        $this->assertTrue($user->verifyLogin($password), 'Password was not changed');
    }


    public function testDeleteWillDelete()
    {
        $user = new UserImpl('root', $this->db);
        $this->assertTrue($user->exists(), 'User does not exist');
        $ret = $user->delete();
        $this->assertTrue($ret, 'Delete did not return true');
        $this->assertFalse($user->exists(), 'User was not removed');
    }


    public function testLoginWillReturnFalseIfUserDoesNotExist()
    {
        $password = 'somePassword';
        $ret = $this->user->setPassword($password);
        $this->assertTrue($ret, 'setPassword failed');
        $ret = $this->user->login($password);
        $this->assertFalse($ret, 'did not return false');

    }

    public function testLoginWillReturnTrueOnSuccess()
    {
        $password = 'somePass';
        $user = new UserImpl('root', $this->db);
        $user->setPassword($password);
        $ret = $user->login($password);
        $this->assertTrue($ret, 'Login failed');
    }

    public function testLoginWillReturnFalseOnInvalidPasswordGiven()
    {
        $password = 'somePass';
        $user = new UserImpl('root', $this->db);
        $user->setPassword($password);
        $ret = $user->login($password . 'Other');
        $this->assertFalse($ret, 'Login did not fail');
    }

    public function testLoginWillReturnFalseOnMultipleLogin()
    {
        $password = 'somePass';
        $user = new UserImpl('root', $this->db);
        $user->setPassword($password);
        $ret = $user->login($password);
        $this->assertTrue($ret, 'Login failed');
        $ret = $user->login($password);
        $this->assertFalse($ret, 'Login did not fail');

    }

    public function testLoginWillReturnFalseOnMultipleLoginDifferentInstances()
    {
        $password = 'somePass';
        $user = new UserImpl('root', $this->db);
        $user->setPassword($password);
        $ret = $user->login($password);
        $this->assertTrue($ret, 'Login failed');
        $user = new UserImpl('root2000', $this->db);
        $user->setPassword($password);
        $user->setMail('test@test.dk');
        $ret = $user->create();
        $this->assertTrue($ret, 'Creation failed');
        $ret = $user->login($password);
        $this->assertFalse($ret, 'Login did not fail');
    }

    public function testIsLoggedInWillReturnFalseIfNotLoggedIn()
    {
        $user = new UserImpl('root', $this->db);
        $this->assertTrue($user->exists(), 'User did not exist');
        $this->assertFalse($user->isLoggedIn(), 'Did not return false');
    }

    public function testIsLoggedInWillReturnTrueIfLoggedIn()
    {
        $password = 'somePassword';
        $user = new UserImpl('root', $this->db);
        $this->assertTrue($user->exists(), 'User did not exist');
        $ret = $user->setPassword($password);
        $this->assertTrue($ret, 'Password was not changed');
        $ret = $user->login($password);
        $this->assertTrue($ret, 'Was not logged in');
        $this->assertTrue($user->isLoggedIn(), 'Did not return true');
    }

    public function testIsLoggedInWillReturnTrueIfLoggedInOnDifferentInstances()
    {
        $password = 'somePassword';
        $user = new UserImpl('root', $this->db);
        $this->assertTrue($user->exists(), 'User did not exist');
        $ret = $user->setPassword($password);
        $this->assertTrue($ret, 'Password was not changed');
        $ret = $user->login($password);
        $this->assertTrue($ret, 'Was not logged in');

        $user = new UserImpl('root', $this->db);
        $this->assertTrue($user->isLoggedIn(), 'Did not return true');
    }

    public function testLogoutWillLogout()
    {
        $password = 'somePassword';
        $user = new UserImpl('root', $this->db);
        $this->assertTrue($user->exists(), 'User did not exist');
        $ret = $user->setPassword($password);
        $this->assertTrue($ret, 'Password was not changed');
        $ret = $user->login($password);
        $this->assertTrue($ret, 'Was not logged in');
        $ret = $user->logout();
        $this->assertFalse($user->isLoggedIn(), 'Did not logout');
        $this->assertTrue($ret, 'Did not return true');
    }

    public function testLogoutWillOnlyLogoutCalledUser()
    {
        $password = 'somePassword';
        $user = new UserImpl('root', $this->db);
        $this->assertTrue($user->exists(), 'User did not exist');
        $ret = $user->setPassword($password);
        $this->assertTrue($ret, 'Password was not changed');
        $ret = $user->login($password);
        $this->assertTrue($ret, 'Was not logged in');

        $this->user->setMail('test@test.dk');
        $this->user->setPassword($password);
        $ret = $this->user->create();
        $this->assertTrue($ret, 'User was not created');
        $ret = $this->user->logout();

        $this->assertFalse($ret, 'Did not return false');

        $this->assertTrue($user->isLoggedIn(), 'Root was logged out');

    }

    public function testLogoutOnDifferentInstanceWillLogout()
    {
        $password = 'somePassword';
        $user = new UserImpl('root', $this->db);
        $this->assertTrue($user->exists(), 'User did not exist');
        $ret = $user->setPassword($password);
        $this->assertTrue($ret, 'Password was not changed');
        $ret = $user->login($password);
        $this->assertTrue($ret, 'Was not logged in');

        $user2 = new UserImpl('root', $this->db);
        $user2->logout();

        $this->assertFalse($user->isLoggedIn(), 'User was logged in');
    }

    public function testGetLastLoginWillReturnLastTimeOfLogin()
    {
        $password = 'somePassword';
        $user = new UserImpl('root', $this->db);
        $this->assertTrue($user->exists(), 'User did not exist');
        $ret = $user->setPassword($password);
        $this->assertTrue($ret, 'Password was not changed');

        $time = $user->getLastLogin();
        $this->assertTrue(is_int($time), 'Did not return int');

        $ret = $user->login($password);
        $this->assertTrue($ret, 'Was not logged in');

        $time2 = $user->getLastLogin();

        $this->assertTrue($time < $time2, 'Did not change time');

    }

    public function testGetLastLoginWillBeNullIfNotLoggedInBefore()
    {
        $this->user->setMail('test@test.dk');
        $this->user->setPassword('somePassword');
        $ret = $this->user->create();
        $this->assertTrue($ret, 'Create was not successful');
        $ret = $this->user->getLastLogin();
        $this->assertNull($ret, 'Did not return null');

    }


    public function testGetLastLoginOnNonExistingUserWillReturnNull()
    {
        $this->user->setMail('test@test.dk');
        $this->user->setPassword('somePassword');
        $this->assertFalse($this->user->exists(), 'User did exist');
        $ret = $this->user->getLastLogin();
        $this->assertNull($ret, 'Did not return null');

    }

    public function testChangePasswordWillPreserveLogin()
    {
        $password = 'somePassword';
        $user = new UserImpl('root', $this->db);
        $user->setPassword($password);
        $ret = $user->login($password);
        $this->assertTrue($ret, 'User was not logged in');
        $user->setPassword('anotherPassword');
        $this->assertTrue($user->isLoggedIn(), 'User was logged out');
    }

    public function testChangeUsernameWillPreserveLogin()
    {
        $password = 'somePassword';
        $user = new UserImpl('root', $this->db);
        $user->setPassword($password);
        $ret = $user->login($password);
        $this->assertTrue($ret, 'User was not logged in');
        $user->setUsername('someUser');
        $this->assertTrue($user->isLoggedIn(), 'User was logged out');
    }


    public function testCreateTwoUsersCanBeDone()
    {
        $user1 = new UserImpl('user1', $this->db);
        $user1->setMail('user1@test.dk');
        $user1->setPassword('user1');

        $user2 = new UserImpl('user2', $this->db);
        $user2->setMail('user2@test.dk');
        $user2->setPassword('user2');

        $ret = $user1->create();
        $this->assertTrue($ret, 'User1 was not created');

        $ret = $user2->create();
        $this->assertTrue($ret, 'User2 was not created');

    }

    public function testDeleteWillNotifyObserver()
    {
        $observer = new StubObserverImpl();
        $user = new UserImpl('root', $this->db);
        $user->attachObserver($observer);
        $this->assertFalse($observer->hasBeenCalled(), 'Observer has been called');
        $user->delete();
        $this->assertTrue($observer->hasBeenCalled(), 'Observer has not been called');
        $this->assertEquals($observer->getLastCallType(), User::EVENT_DELETE, 'Event did not match');
    }

    public function testLoginWillNotifyObserver()
    {
        $observer = new StubObserverImpl();
        $user = new UserImpl('root', $this->db);
        $user->attachObserver($observer);
        $user->setPassword($pass = "pass");
        $this->assertFalse($observer->hasBeenCalled(), 'Observer has been called');
        $this->assertTrue($user->login($pass));
        $this->assertTrue($observer->hasBeenCalled(), 'Observer has not been called');
        $this->assertEquals($observer->getLastCallType(), User::EVENT_LOGIN, 'Event did not match');
    }

    public function testChangePasswordWillNotLogoutAndLogin()
    {
        $observer = new StubObserverImpl();
        $user = new UserImpl('root', $this->db);
        $user->setPassword($pass = "pass");
        $user->login($pass);
        $user->attachObserver($observer);
        $user->setPassword($pass = "pass2");
        $this->assertFalse($observer->hasBeenCalled(), 'Observer has been called');

    }

    public function testChangeUsernameWillNotifyObserver()
    {
        $observer = new StubObserverImpl();
        $user = new UserImpl('root', $this->db);
        $user->attachObserver($observer);
        $this->assertFalse($observer->hasBeenCalled(), 'Observer has been called');
        $user->setUsername('root2');
        $this->assertTrue($observer->hasBeenCalled(), 'Observer has not been called');
        $this->assertEquals($observer->getLastCallType(), User::EVENT_USERNAME_UPDATE, 'Event did not match');

    }

    public function testChangeParentWillNotifyObserver()
    {
        $observer = new StubObserverImpl();
        $user = new UserImpl('user2', $this->db);
        $user->attachObserver($observer);
        $this->assertFalse($observer->hasBeenCalled(), 'Observer has been called');
        $user->setParent('root');
        $this->assertTrue($observer->hasBeenCalled(), 'Observer has not been called');
        $this->assertEquals($observer->getLastCallType(), User::EVENT_PARENT_UPDATE, 'Event did not match');

    }

    public function testDetachObserverWillDetachObserver()
    {
        $observer = new StubObserverImpl();
        $user = new UserImpl('root', $this->db);
        $user->attachObserver($observer);
        $user->detachObserver($observer);
        $user->delete();
        $this->assertFalse($observer->hasBeenCalled(), 'Observer has been called');
    }


    public function testGetParentWillReturnNullOnNoParent()
    {
        $this->assertNull($this->user->getParent(), 'Did not return null');
    }

    public function testSetParentWillSetParent()
    {
        $user = new UserImpl('testUser', $this->db);
        $user->setMail('test@test.dk');
        $user->setPassword('SomePass');
        $ret = $user->setParent('root');
        $this->assertTrue($ret);
        $this->assertEquals('root', $user->getParent());
    }

    public function testSetParentWillBePersistent()
    {
        $username = 'testUser';
        $user = new UserImpl($username, $this->db);
        $user->setMail('test@test.dk');
        $user->setPassword('SomePass');
        $ret = $user->setParent('root');
        $this->assertTrue($ret);
        $user->create();
        $user = new UserImpl($username, $this->db);
        $this->assertEquals('root', $user->getParent());
    }

    public function testSetParentToSelfWillReturnFalse()
    {
        $ret = $this->user->setParent($this->user->getUsername());
        $this->assertFalse($ret, 'Did not return false');
        $this->assertNull($this->user->getParent());
    }

    public function testSetParentWillReturnFalseIfParentDoesNotExist()
    {
        $user = new UserImpl('root', $this->db);
        $ret = $user->setParent('notAUser');
        $this->assertFalse($ret);
        $this->assertNull($user->getParent());
    }

    public function testCircularParentingWillReturnFalse()
    {
        $user = new UserImpl('root', $this->db);
        $user2 = new UserImpl('user2', $this->db);
        $user2->setMail('test@test.dk');
        $user2->setPassword('somePassword');
        $ret = $user2->setParent($user->getUsername());
        $this->assertTrue($ret);
        $user2->create();
        $user3 = new UserImpl('user3', $this->db);
        $user3->setMail('test@test.dk');
        $user3->setPassword('somePassword');
        $ret = $user3->setParent($user2->getUsername());
        $this->assertTrue($ret);
        $user3->create();
        $ret = $user->setParent($user3->getUsername());
        $this->assertFalse($ret);
        $this->assertNull($user->getParent());
    }

    public function testChangeUsernameOfParentWillWork()
    {
        $user = new UserImpl('root', $this->db);
        $user2 = new UserImpl('user2', $this->db);
        $user2->setMail('test@test.dk');
        $user2->setPassword('somePassword');
        $user2->setParent($user->getUsername());
        $user2->create();
        $newUsername = 'root2';
        $ret = $user->setUsername($newUsername);
        $this->assertTrue($ret, 'Did not return true');
        $this->assertEquals($newUsername, $user->getUsername(), 'Usernames did not match');
    }

    public function testDeleteParentWillReturnFalse()
    {
        $user = new UserImpl('root', $this->db);
        $user2 = new UserImpl('user2', $this->db);
        $user2->setMail('test@test.dk');
        $user2->setPassword('somePassword');
        $user2->setParent($user->getUsername());
        $user2->create();
        $this->assertFalse($user->delete(), 'Did not return false');
    }

    public function testGetUserPrivilegesRightInstance()
    {
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\user\\UserPrivileges", $this->user->getUserPrivileges());
    }

    public function testGetUserPrivilegesReturnSameInstance()
    {
        $this->assertTrue($this->user->getUserPrivileges() === $this->user->getUserPrivileges());
    }


    public function testGetUserVariablesReturnsRightInstance()
    {
        $this->assertInstanceOf("ChristianBudde\\Part\\model\\Variables", $this->user->getUserVariables());
    }

    public function testGetUserVariablesReturnsSameInstance()
    {
        $this->assertTrue($this->user->getUserVariables() === $this->user->getUserVariables());
    }

    public function testUserIsJSONObjectSerializable()
    {
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Object', $o = $this->user->jsonObjectSerialize());
        $this->assertEquals('user', $o->getName());
    }


    public function testGetTokenReturnsToken(){
        $this->assertNotEquals($this->user->getUserToken(), $this->user2->getUserToken());
    }

    public function testGetTokenReturnsTokenEqualOnSameUser(){
        $this->assertEquals($this->user->getUserToken(), $this->user->getUserToken());
    }
    public function testGetTokenDoesNotChangeOnUsernameChangeOrPasswordChange(){
        $token = $this->user->getUserToken();
        $this->user->setUsername("bob");
        $token2 = $this->user->getUserToken();
        $this->user->setPassword("new password");
        $token3 = $this->user->getUserToken();
        $this->assertEquals($token, $token2);
        $this->assertEquals($token2, $token3);

    }

    public function testGetTokenReturnsNewTokenAfterLogin(){
        //$h1 = $this->user2->getUserToken();
        $this->user2->setPassword($password = "some super secret password");
        $h2 = $this->user2->getUserToken();
        $this->assertTrue($this->user2->login($password));
        $h3 = $this->user2->getUserToken();
        //$this->assertNotEquals($h1, $h2);
        $this->assertNotEquals($h2, $h3);
        //$this->assertNotEquals($h1, $h3);

    }

    public function getSetUpOperation()
    {
        $cascadeTruncates = true;
        return new PHPUnit_Extensions_Database_Operation_Composite(array(new TruncateOperation($cascadeTruncates), PHPUnit_Extensions_Database_Operation_Factory::INSERT()));
    }

    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        $pdo = self::$pdo;
        return $this->createDefaultDBConnection($pdo);
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/../mysqlXML/UserImplTest.xml');
    }


}
