<?php

namespace ChristianBudde\Part\controller\ajax\type_handler;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\BackendSingletonContainerImpl;
use ChristianBudde\Part\ConfigImpl;
use ChristianBudde\Part\controller\ajax\ServerImpl;
use ChristianBudde\Part\controller\json\JSONFunctionImpl;
use ChristianBudde\Part\controller\json\Response;
use ChristianBudde\Part\controller\json\TypeImpl;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\model\user\UserLibrary;
use ChristianBudde\Part\util\CustomDatabaseTestCase;
use ChristianBudde\Part\util\file\FolderImpl;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/30/14
 * Time: 5:17 PM
 */
class BackendTypeHandlerImplTest extends CustomDatabaseTestCase
{
    /** @var  ServerImpl */
    private $server;
    /** @var  BackendSingletonContainer */
    private $container;
    /** @var  UserLibrary */
    private $userLibrary;
    private $config;
    /** @var  BackendTypeHandlerImpl */
    private $typeHandler;
    /** @var  User */
    private $rootUser;


    function __construct()
    {
        parent::__construct($GLOBALS['MYSQL_XML_DIR'] . '/BackendAJAXTypeHandlerImplTest.xml');
    }


    protected function setUp()
    {

        parent::setUp();
        $host = self::$mysqlOptions->getHost();
        $username = self::$mysqlOptions->getUsername();
        $password = self::$mysqlOptions->getPassword();
        $database = self::$mysqlOptions->getDatabase();
        $tmpFolder = "/tmp/cbweb-test/" . uniqid();
        $folder = new FolderImpl($tmpFolder);
        $folder->create(true);
        $logFile = $tmpFolder . "logFile";
        $this->config = new ConfigImpl(simplexml_load_string(/** @lang XML */
            "<?xml version='1.0' encoding='UTF-8'?>
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
    <enableUpdater>true</enableUpdater>
    <debugMode>false</debugMode>
    <tmpFolder path='$tmpFolder'/>
    <preTasks>
        <class >ChristianBudde\\Part\\util\\script\\UserLoginCheckPreScript</class>
        <class >ChristianBudde\\Part\\util\\script\\UserLoginUpdateCheckPreScript</class>
        <class >ChristianBudde\\Part\\util\\script\\RequireHTTPSPreScript</class>
    </preTasks>
    <log path='$logFile' />
</config>"), $tmpFolder);
        $this->container = new BackendSingletonContainerImpl($this->config);
        $this->typeHandler = new BackendTypeHandlerImpl($this->container);
        $this->setUpServer();
        $this->rootUser = $this->container->getUserLibraryInstance()->getUser('root');
        $this->rootUser->getUserPrivileges()->addRootPrivileges();
        $this->userLibrary = $this->container->getUserLibraryInstance();
    }


    public function testCanHandleIsFalse(){
        $this->assertFalse($this->typeHandler->canHandle('User', new JSONFunctionImpl('getId', new TypeImpl('User'))));
    }

    public function testHandleGivesException(){
        $this->setExpectedException('Exception');
        $this->typeHandler->handle('User', new JSONFunctionImpl('getId', new TypeImpl('User')));
    }

    public function testHasNoType(){
        $this->assertEquals([], $this->typeHandler->listTypes());
        $this->assertFalse($this->typeHandler->hasType("User"));
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

    public function testUserLibraryUserLoginReturnsToken(){
        $user = $this->setUpRootUserLogin($password = "password");
        $user->logout();
        $username = $user->getUsername();
        $response = $this->assertSuccessResponse("UserLibrary.userLogin('$username', '$password')");
        $this->assertEquals($response->getPayload(), $this->userLibrary->getUserSessionToken());

    }

    public function testUserLibraryLoginDoesNotReuseToken(){
        $user = $this->setUpRootUserLogin($password = "password");
        $user->logout();
        $username = $user->getUsername();
        $response1 = $this->assertSuccessResponse("UserLibrary.userLogin('$username', '$password')");
        sleep(1);
        $user->logout();
        $response2 = $this->assertSuccessResponse("UserLibrary.userLogin('$username', '$password')");
        $this->assertNotEquals($response1->getPayload(), $response2->getPayload());

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
        $this->assertErrorResponse("UserLibrary.deleteUser(UserLibrary.getUserLoggedIn())", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.getUserLoggedIn()", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.getInstance()", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.getUser('someUser')", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.getParent(UserLibrary.getUserLoggedIn())", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.getChildren(UserLibrary.getUserLoggedIn())", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("UserLibrary.getChildren(UserLibrary.getUserLoggedIn())", Response::ERROR_CODE_UNAUTHORIZED);
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
        $this->assertErrorResponse($f= "User.getInstance()", Response::ERROR_CODE_NO_SUCH_FUNCTION);
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

    public function testCallPageOrderWithoutPage(){
        $pageOrder = $this->container->getPageOrderInstance();
        $this->assertResponsePayloadEquals("PageOrder.getPageOrder()", $pageOrder->getPageOrder());
    }

    public function testCallingNonAuthorizedFunctionsIsOk(){
        $currentPage = "PageOrder.getCurrentPage()";
        $pageOrder = $this->container->getPageOrderInstance();
        $this->assertResponsePayloadEquals("PageOrder.getInstance()", $pageOrder);
        $this->assertResponsePayloadEquals($currentPage, $pageOrder->getCurrentPage());
        $this->assertResponsePayloadEquals("PageOrder.getPageOrder($currentPage)", $pageOrder->getPageOrder($pageOrder->getCurrentPage()));
        $this->assertResponsePayloadEquals("PageOrder.isActive($currentPage)", $pageOrder->isActive($pageOrder->getCurrentPage()));
        $this->assertResponsePayloadEquals("PageOrder.listPages()", $pageOrder->listPages());
        $this->assertResponsePayloadEquals("PageOrder.getPage($currentPage.getID())", $pageOrder->getPage($pageOrder->getCurrentPage()->getID()));
        $this->assertResponsePayloadEquals("PageOrder.getPagePath($currentPage)", $pageOrder->getPagePath($pageOrder->getCurrentPage()));
    }

    public function testCallingWithWrongAuthIsNotOk(){
        $this->setUpPageUserLogin();
        $currentPage = "PageOrder.getCurrentPage()";
        $this->assertErrorResponse("PageOrder.deletePage($currentPage)", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("PageOrder.deactivatePage($currentPage)", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("PageOrder.setPageOrder($currentPage)", Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertErrorResponse("PageOrder.createPage('test_title')", Response::ERROR_CODE_UNAUTHORIZED);

    }


    public function testCreatePageWithEmptyTitleIsNotOk(){
        $this->setUpSiteUserLogin();
        $this->assertErrorResponse("PageOrder.createPage('')", Response::ERROR_CODE_INVALID_PAGE_TITLE);
    }

    public function testCreatePageIsOk(){
        $this->setUpSiteUserLogin();

        $response = $this->assertSuccessResponse("PageOrder.createPage('New Title')");
        $p = $this->container->getPageOrderInstance()->getPage("new_title");
        $this->assertEquals($p, $response->getPayload());

    }

    public function testModifyPageIsOkWithRightPrivileges(){
        $page = $this->container->getPageOrderInstance()->getCurrentPage();
        $user = $this->setUpPageUserLogin();
        $this->assertErrorResponse('Page.setTitle("New Title")', Response::ERROR_CODE_UNAUTHORIZED);
        $user->getUserPrivileges()->addPagePrivileges($page);
        $this->assertSuccessResponse('Page.setTitle("New Title")');
        $this->assertEquals($page->getTitle(), 'New Title');

    }


    public function testDeleteIsNotOkayWithoutSitePrivileges(){
        $page = $this->container->getPageOrderInstance()->getCurrentPage();
        $u = $this->setUpPageUserLogin();
        $this->assertErrorResponse('Page.delete()', Response::ERROR_CODE_UNAUTHORIZED);
        $this->assertTrue($page->exists());
        $u->logout();
        $this->setUpSiteUserLogin();
        $this->assertSuccessResponse('Page.delete()');
        $this->assertFalse($page->exists());
    }

    public function testDeactivatePageIsOk(){
        $this->setUpSiteUserLogin();
        $currentPage = "PageOrder.getCurrentPage()";
        $this->assertSuccessResponse("PageOrder.deactivatePage($currentPage)");
        $this->assertFalse($this->container->getPageOrderInstance()->isActive($this->container->getPageOrderInstance()->getCurrentPage()));
    }


    public function testLoggerHasSitePrivileges(){

        $u = $this->setUpPageUserLogin();
        $this->assertErrorResponse('Logger.clearLog()', Response::ERROR_CODE_UNAUTHORIZED);

        $u->logout();
        $this->setUpSiteUserLogin();
        $this->assertSuccessResponse('Logger.clearLog()');

    }

    public function testLoggerLogIsOk(){
        $this->assertErrorResponse('Logger.alert("alert")', Response::ERROR_CODE_UNAUTHORIZED);
        $this->setUpPageUserLogin();
        $this->assertSuccessResponse("Logger.alert('alert')");

    }

    public function testUpdaterIsNotOkWithoutSitePrivileges(){
        $u = $this->setUpPageUserLogin();
        $this->assertErrorResponse('Updater.checkForUpdates()', Response::ERROR_CODE_UNAUTHORIZED);

        $u->logout();
        $this->setUpSiteUserLogin();
        $this->assertSuccessResponse('Updater.checkForUpdates()');

    }

    public function  testArrayAccess(){
        $_POST['test'] = 1;
        $_GET['test'] = 2;
        $this->setUpServer();
        $this->assertSuccessResponse('POST["test"]');
        $this->assertSuccessResponse('GET["test"]');
        $this->assertResponsePayloadEquals("POST['test']", 1);
        $this->assertResponsePayloadEquals("GET['test']", 2);
    }



    public function testLoggerCanLogPost(){
        $this->setUpPageUserLogin();
        $_POST['a'] = 'test';
        $_POST['b'] = '{"stact trace":"#0      ImageEditProperties.toFunctionString (package:part/src/elements_image_editor.dart:122:5)\n#1      ImageEditorHandler._setUpListeners.<anonymous closure> (package:part/src/elements_image_editor.dart:569:55)\n#2      _rootRunUnary (dart:async/zone.dart:906)\n#3      _CustomZone.runUnary (dart:async/zone.dart:804)\n#4      _CustomZone.runUnaryGuarded (dart:async/zone.dart:712)\n#5      _CustomZone.bindUnaryCallback.<anonymous closure> (dart:async/zone.dart:738)\n"}';
        $this->assertSuccessResponse('Logger.log(8,Parser.parseJson(POST["a"]),Parser.parseJson(POST["b"]))');
    }


    /**
     * @param $functionString
     * @param $equals
     * @return Response
     */
    public function assertResponsePayloadEquals($functionString, $equals)
    {
        $response = $this->server->handleFromFunctionString($functionString, $this->userLibrary->getUserSessionToken());
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $response);
        $this->assertEquals($response->getResponseType(), Response::RESPONSE_TYPE_SUCCESS);
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
        $response = $this->server->handleFromFunctionString($functionString, $this->userLibrary->getUserSessionToken());
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $response);
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

        $response = $this->server->handleFromFunctionString($functionString, $this->userLibrary->getUserSessionToken());
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $response);
        $this->assertEquals(Response::RESPONSE_TYPE_SUCCESS, $response->getResponseType());
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
        $this->server = new ServerImpl($this->container);
        $this->server->registerHandler($this->typeHandler);

    }

}