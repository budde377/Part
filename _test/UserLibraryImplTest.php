<?php
require_once dirname(__FILE__) . '/../_test/TruncateOperation.php';
require_once dirname(__FILE__) . '/../_test/MySQLConstants.php';
require_once dirname(__FILE__) . '/../_test/_stub/StubDBImpl.php';
require_once dirname(__FILE__) . '/../_class/UserLibraryImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 23/07/12
 * Time: 16:49
 */
class UserLibraryImplTest extends PHPUnit_Extensions_Database_TestCase
{

    /** @var $db StubDBImpl */
    private $db;
    /** @var $pdo PDO */
    private $pdo;
    /** @var $user UserLibraryImpl */
    private $library;


    public function setUp()
    {
        parent::setUp();
        @session_destroy();
        @session_start();
        $this->pdo = new PDO('mysql:dbname=' . self::database . ';host=' . self::host, self::username, self::password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_PERSISTENT=>true));
        $this->db = new StubDBImpl();
        $this->db->setConnection($this->pdo);
        $this->library = new UserLibraryImpl($this->db);
    }


    public function testListUsersWillListUsers()
    {
        $ret = $this->library->listUsers();
        $this->assertTrue(is_array($ret), 'Did not return array');
        $this->assertEquals(3, count($ret), 'Did not return array of right length');
    }

    public function testCreateUserWillCreateUser()
    {
        $ret = $this->library->createUser('user4', 'password', 'user3@test.dk',$this->library->getUser('user1'));
        $this->assertInstanceOf('User', $ret, 'Did not return user');
        $this->assertTrue($ret->exists(), 'User does not exist');
    }

    public function testCreateExistingUserWillReturnFalse()
    {
        $ret = $this->library->createUser('user1', 'somePass', 'test@test.dk',$this->library->getUser('user1'));
        $this->assertFalse($ret);
    }

    public function testCreatedUserWillBeInUserList()
    {
        $user = $this->library->createUser('user4', 'password', 'user3@test.dk',$this->library->getUser('user1'));
        $ret = $this->library->listUsers();
        $this->assertEquals(4, count($ret));

        $this->assertTrue($this->userInList($ret, $user), 'User was not in list');
    }

    public function testCreateUserWillSetParent(){
        $user1 = $this->library->getUser('user1');
        $user2 = $this->library->createUser('user4', 'password', 'user3@test.dk',$this->library->getUser('user1'));
        $this->assertEquals($user1->getUsername(),$user2->getParent(),'Parent did not match');
    }

    public function testDeleteUserWillDeleteUser()
    {
        $list = $this->library->listUsers();
        /** @var $user User */
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

    public function testGetLoggedInWillReturnLoggedInWithUserLoggedIn(){
        $password = 'somePassword';
        $list = $this->library->listUsers();
        /** @var $user User */
        $user = $list[1];
        $ret = $user->setPassword($password);
        $this->assertTrue($ret,'Could not set password');
        $ret = $user->login($password);
        $this->assertTrue($ret,'Could not log in');

        $loggedIn = $this->library->getUserLoggedIn();
        $this->assertTrue($loggedIn === $user,'Did not return logged in user');
    }
/**
    public function testGetLoggedInWillReturnLoggedInWithLoginDoneOnInstanceNIL(){
        $password = 'somePassword';
        $user = new UserImpl('user1',$this->db);
        $ret = $user->setPassword($password);
        $this->assertTrue($ret,'Could not set password');
        $ret = $user->login($password);
        $this->assertTrue($ret,'Could not log in');

        $loggedIn = $this->library->getUserLoggedIn();
        $this->assertEquals($user->getUsername(),$loggedIn->getUsername(),'Usernames did not match');
    }
**/
    public function testGetLoggedInWillReturnNullWithUserLoggedOut(){
        $password = 'somePassword';
        $user = new UserImpl('user1',$this->db);
        $ret = $user->setPassword($password);
        $this->assertTrue($ret,'Could not set password');
        $ret = $user->login($password);
        $this->assertTrue($ret,'Could not log in');
        $ret = $user->logout();
        $this->assertTrue($ret,'Did not log out');

        $loggedIn = $this->library->getUserLoggedIn();
        $this->assertNull($loggedIn,'Was not null');
    }

    public function testDeleteOnObjectWillReflectInList(){
        $list = $this->library->listUsers();
        /** @var $user User */
        $user = $list[2];
        $user->delete();
        $list = $this->library->listUsers();
        $this->assertFalse($this->userInList($list,$user),'Did not delete in library');
    }

    public function testGetUserWillReturnNullIfUserNIL(){
        $user = $this->library->getUser('NonExistingUser');
        $this->assertNull($user);

    }

    public function testGetUSerWillReturnUserIfNotNIL(){
        $user = $this->library->getUser('user2');
        $this->assertInstanceOf('User',$user,'Did not return instance of User');
        $this->assertEquals('user2',$user->getUsername(),'Usernames did not match');
    }


    public function testGetParentWillReturnParentAsUserInstance(){
        $user = $this->library->getUser('user2');
        $user = $this->library->getParent($user);
        $this->assertInstanceOf('User',$user);
        $this->assertEquals('user1',$user->getUsername());
        $this->assertFalse($this->library->deleteUser($user),'Could not delete user');
    }

    public function testGetParentWillReturnNullIfNoParent(){
        $user = $this->library->getUser('user1');
        $this->assertNull($this->library->getParent($user));
    }

    public function testChangeUsernameWillStillWork(){
        $user = $this->library->getUser('user1');
        $user->setUsername('user11');
        $user = $this->library->getUser('user11');
        $this->assertInstanceOf('User',$user);
        $this->assertEquals('user11',$user->getUsername(),'User names did not match');
    }

    public function testGetChildrenWillReturnChildren(){
        $parent = $this->library->getUser('user1');
        $ret = $this->library->getChildren($parent);
        $this->assertTrue(is_array($ret),'Did not return array');
        $this->assertEquals(2,count($ret),'Did not return array of right size');
        $this->assertInstanceOf('User',$ret[0]);
        $this->assertInstanceOf('User',$ret[1]);
        $this->assertTrue($ret[0]->getUsername() == 'user2' || $ret[0]->getUsername() == 'user3');
        $this->assertTrue($ret[1]->getUsername() == 'user3' || $ret[1]->getUsername() == 'user2');

    }

    public function testDeleteParentWillChangeParentOfChildren(){
        $list = $this->library->listUsers();
        /** @var $user1 UserImpl */
        $user1 = $list[0];
        /** @var $user2 UserImpl */
        $user2 = $list[1];
        /** @var $user3 UserImpl */
        $user3 = $list[2];

        $ret = $this->library->deleteUser($user2);
        $this->assertTrue($ret,'Did not return true');
        $this->assertEquals($user1->getUsername(),$user3->getParent(),'Parent did not match');
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
        $pdo = new PDO('mysql:dbname=' . self::database . ';host=' . self::host, self::username, self::password);
        return $this->createDefaultDBConnection($pdo);
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/_mysqlXML/UserLibraryImplTest.xml');
    }

    private function userInList($ret, User $user)
    {
        $success = false;
        foreach ($ret as $u) {
            $success = ($u === $user || $success == true);
        }
        return $success;
    }

    const database = MySQLConstants::MYSQL_DATABASE;
    const password = MySQLConstants::MYSQL_PASSWORD;
    const username = MySQLConstants::MYSQL_USERNAME;
    const host = MySQLConstants::MYSQL_HOST;
}
