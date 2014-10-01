<?php

namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\BackendSingletonContainer;
use ChristianBudde\cbweb\BackendSingletonContainerImpl;
use ChristianBudde\cbweb\ConfigImpl;
use ChristianBudde\cbweb\controller\ajax\AJAXServerImpl;
use ChristianBudde\cbweb\controller\ajax\BackendAJAXTypeHandlerImpl;
use ChristianBudde\cbweb\controller\json\Response;
use ChristianBudde\cbweb\model\user\User;
use ChristianBudde\cbweb\model\user\UserLibrary;
use ChristianBudde\cbweb\test\util\CustomDatabaseTestCase;
use ChristianBudde\cbweb\util\file\FolderImpl;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/30/14
 * Time: 5:17 PM
 */
class BackendAJAXTypeHandlerImplTest extends CustomDatabaseTestCase
{
    /** @var  AJAXServerImpl */
    private $server;
    /** @var  BackendSingletonContainer */
    private $container;
    /** @var  UserLibrary */
    private $userLibrary;
    private $config;

    private $typeHandler;
    /** @var  User */
    private $rootUser;


    function __construct()
    {
        parent::__construct(dirname(__FILE__) . '/../mysqlXML/BackendAJAXTypeHandlerImplTest.xml');
    }


    protected function setUp()
    {

        parent::setUp();
        $host = self::$mysqlOptions->getHost();
        $username = self::$mysqlOptions->getUsername();
        $password = self::$mysqlOptions->getPassword();
        $database = self::$mysqlOptions->getDatabase();
        $mHost = self::$mailMySQLOptions->getHost();
        $mUsername = self::$mailMySQLOptions->getUsername();
        $mDatabase = self::$mailMySQLOptions->getDatabase();
        $tmpFolder = "/tmp/cbweb/test/" . uniqid();
        $folder = new FolderImpl($tmpFolder);
        $folder->create(true);
        $logFile = $tmpFolder . "logFile";
        $this->config = new ConfigImpl(simplexml_load_string("<?xml version='1.0' encoding='UTF-8'?>
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
        <MailMySQLConnection>
        <host>$mHost</host>
        <database>$mDatabase</database>
        <username>$mUsername</username>
    </MailMySQLConnection>
    <enableUpdater>true</enableUpdater>
    <debugMode>false</debugMode>
    <tmpFolder path='$tmpFolder'/>
    <preScripts>
        <class >ChristianBudde\\cbweb\\util\\script\\UserLoginCheckPreScript</class>
        <class >ChristianBudde\\cbweb\\util\\script\\UserLoginUpdateCheckPreScript</class>
        <class >ChristianBudde\\cbweb\\util\\script\\RequireHTTPSPreScript</class>
    </preScripts>
    <log path='$logFile' />
</config>"), $tmpFolder);
        $this->container = new BackendSingletonContainerImpl($this->config);
        $this->typeHandler = new BackendAJAXTypeHandlerImpl($this->container);
        $this->setUpServer();
        $this->rootUser = $this->container->getUserLibraryInstance()->getUser('root');
        $this->rootUser->getUserPrivileges()->addRootPrivileges();
        $this->userLibrary = $this->container->getUserLibraryInstance();
    }

    public function setUpRootUserLogin($password = null)
    {
        if ($password == null) {
            $password = uniqid();
        }
        $userLib = $this->container->getUserLibraryInstance();
        $user = $userLib->createUser(uniqid(), $password, "test@example.com", $this->rootUser);
        $user->getUserPrivileges()->addRootPrivileges();
        $user->login($password);
        return $user;
    }

    public function setUpSiteUserLogin($password = null)
    {
        if ($password == null) {
            $password = uniqid();
        }
        $userLib = $this->container->getUserLibraryInstance();
        $user = $userLib->createUser(uniqid(), $password, "test@example.com", $this->rootUser);
        $user->getUserPrivileges()->addSitePrivileges();
        $user->login($password);
        return $user;
    }

    public function setUpPageUserLogin($password = null)
    {
        if ($password == null) {
            $password = uniqid();
        }
        $userLib = $this->container->getUserLibraryInstance();
        $user = $userLib->createUser(uniqid(), $password, "test@example.com", $this->rootUser);
        $user->login($password);
        return $user;
    }


    public function testUserLibraryUserLogin()
    {
        $user = $this->setUpRootUserLogin($password = "password");
        $username = $user->getUsername();
        $this->assertErrorResponse("UserLibrary.userLogin('$username', '$password')", Response::ERROR_CODE_INVALID_LOGIN);
        $user->logout();
        $this->assertSuccessResponse("UserLibrary.userLogin('$username', '$password')");
        $user->logout();
        $this->assertErrorResponse("UserLibrary.userLogin('$username', 'invalidPassword')", Response::ERROR_CODE_INVALID_LOGIN);
    }

    public function testUserLibraryCreateUser()
    {
        $currentUser = $this->setUpSiteUserLogin();
        $this->assertErrorResponse('UserLibrary.createUserFromMail()',Response::ERROR_CODE_NO_SUCH_FUNCTION);
        $this->assertErrorResponse('UserLibrary.createUserFromMail("test", "root")',Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse('UserLibrary.createUserFromMail("test", "site")',Response::ERROR_CODE_INVALID_MAIL);
        $this->assertSuccessResponse('UserLibrary.createUserFromMail("test@example.com", "site")');
        $userLibrary = $this->container->getUserLibraryInstance();
        $user = $userLibrary->getUser('test');
        $this->assertTrue($user->exists());
        $this->assertEquals('test@example.com', $user->getMail());
        $this->assertEquals($user->getParent(), $currentUser->getUsername());
    }

    public function testUserLibraryFunctionsNotLoggedIn()
    {
        $this->assertErrorResponse("UserLibrary.listUsers()", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.deleteUser(null)", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.getUserLoggedIn()", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.getInstance()", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.getUser('someUser')", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.getParent(null)", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.getChildren(null)", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.getChildren(null)", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.createUserFromMail('test@example.com', 'root')", Response::ERROR_CODE_UNAUTHORIZED);

        $this->assertErrorResponse("UserLibrary.createUser('test', 'test', 'test@example.com', null)", Response::ERROR_CODE_NO_SUCH_FUNCTION);

    }

    public function testFunctionsReturnsRight(){

        $u = $this->setUpPageUserLogin($password = "password");

        $this->assertResponsePayloadEquals("UserLibrary.listUsers()", $this->userLibrary->listUsers());
        $this->assertResponsePayloadEquals("UserLibrary.getUserLoggedIn()", $this->userLibrary->getUserLoggedIn());
        $this->assertResponsePayloadEquals($userString = "UserLibrary.getUser('{$u->getUsername()}')", $this->userLibrary->getUser($u->getUsername()));
        $this->assertResponsePayloadEquals("UserLibrary.getParent($userString)", $this->userLibrary->getParent($u));
        $this->assertResponsePayloadEquals("UserLibrary.getChildren($userString)", $this->userLibrary->getChildren($u));
        $this->assertResponsePayloadEquals("UserLibrary.getParent($userString)", $this->userLibrary->getParent($u));
        $u2 = $this->setUpPageUserLogin();
        $u2->setParent($u->getUsername());
        $u->login($password);
        $userString = "UserLibrary.getUser('{$u2->getUsername()}')";
        $this->assertSuccessResponse("UserLibrary.deleteUser($userString)");
        $this->assertFalse($u2->exists());
    }

    public function testUserLibraryGetInstanceReturnsInstance(){
        $this->setUpPageUserLogin();
        $this->assertResponsePayloadEquals('UserLibrary.getInstance()', $this->container->getUserLibraryInstance());
    }

    public function testUserGetInstance(){
        $this->assertSuccessResponse($f = "User.getInstance()");
        $this->assertResponsePayloadEquals($f, null);
        $u = $this->setUpPageUserLogin();
        $this->setUpServer();
        $this->assertSuccessResponse($f);
        $this->assertResponsePayloadEquals($f, $u);
    }

    public function testGettersAreOk(){
        $u = $this->setUpPageUserLogin();
        $this->setUpServer();
        $this->assertResponsePayloadEquals("User.getUsername()", $u->getUsername());
        $this->assertResponsePayloadEquals("User.getMail()", $u->getMail());
        $this->assertResponsePayloadEquals("User.getLastLogin()", $u->getLastLogin());
        $this->assertResponsePayloadEquals("User.getParent()", $u->getParent());
        $this->assertResponsePayloadEquals("User.getUserPrivileges()", $u->getUserPrivileges());
        $this->assertResponsePayloadEquals("User.getUniqueId()", $u->getUniqueId());
    }

    public function testSettersAreOk(){
        $u = $this->setUpPageUserLogin($password = "asd");
        $this->setUpServer();
        $this->assertSuccessResponse("User.setUsername('bob')");
        $this->assertEquals("bob", $u->getUsername());
        $this->assertSuccessResponse("User.setMail('bob')");
        $this->assertEquals('test@example.com', $u->getMail());
        $this->assertSuccessResponse("User.setMail('test2@example.com')");
        $this->assertEquals('test2@example.com', $u->getMail());
        $this->assertErrorResponse("User.delete()", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertTrue($u->exists());
        $this->assertErrorResponse("User.verifyLogin('$password')", Response::ERROR_CODE_NO_SUCH_FUNCTION);
    }

    public function testUserSetPassword(){
        $u = $this->setUpPageUserLogin($password = "asd");
        $this->setUpServer();
        $this->assertErrorResponse("User.setPassword('bob')", Response::ERROR_CODE_NO_SUCH_FUNCTION);
        $this->assertErrorResponse("User.setPassword('bob', 'bob')", Response::ERROR_CODE_WRONG_PASSWORD);
        $this->assertFalse($u->verifyLogin('bob'));
        $this->assertSuccessResponse("User.setPassword('$password', 'bob')");
        $this->assertTrue($u->verifyLogin('bob'));

    }

    public function testUserDeleteNonChildIsNotOk(){
        $this->setUpPageUserLogin();
        $this->assertErrorResponse("UserLibrary.getUser('root').delete()", Response::ERROR_CODE_UNAUTHORIZED);

    }

    public function testUserDeleteChildIsOk(){
        $u1 = $this->setUpPageUserLogin($password = "password");

        $u2 = $this->setUpPageUserLogin();
        $u2->setParent($u1->getUsername());

        $this->assertSuccessResponse("UserLibrary.getUser('{$u2->getUsername()}').delete()");

    }





    /**
     * @param $functionString
     * @param $equals
     * @return Response
     */
    public function assertResponsePayloadEquals($functionString, $equals)
    {
        $response = $this->server->handleFromFunctionString($functionString);
        $this->assertInstanceOf('ChristianBudde\cbweb\controller\json\Response', $response);
        $this->assertEquals($equals, $response->getPayload());
        return $response;

    }

    /**
     * @param $functionString
     * @param int $errorCode
     * @return Response
     */
    public function assertErrorResponse($functionString, $errorCode = null)
    {
        $response = $this->server->handleFromFunctionString($functionString);
        $this->assertInstanceOf('ChristianBudde\cbweb\controller\json\Response', $response);
        $this->assertEquals($response->getResponseType(), Response::RESPONSE_TYPE_ERROR);
        if ($errorCode != null) {
            $this->assertEquals($errorCode, $response->getErrorCode());
        }
        return $response;
    }

    /**
     * @param $functionString
     * @return Response
     */
    public function assertSuccessResponse($functionString)
    {
        $response = $this->server->handleFromFunctionString($functionString);
        $this->assertInstanceOf('ChristianBudde\cbweb\controller\json\Response', $response);
        $this->assertEquals($response->getResponseType(), Response::RESPONSE_TYPE_SUCCESS);
        return $response;
    }

    /**
     * @param array $functionStrings
     * @param null $errorCode
     */
    public function assertAllErrorResponse(array $functionStrings, $errorCode = null)
    {
        foreach ($functionStrings as $functionString) {
            $this->assertErrorResponse($functionString, $errorCode);
        }

    }

    /**
     * @param array $functionStrings
     */
    public function assertAllSuccessResponse(array $functionStrings)
    {
        foreach ($functionStrings as $functionString) {
            $this->assertSuccessResponse($functionString);
        }

    }

    private function setUpServer()
    {
        $this->server = new AJAXServerImpl($this->container);
        $this->server->registerHandler($this->typeHandler);

    }

}