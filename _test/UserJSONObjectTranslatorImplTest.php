<?php
require_once  dirname(__FILE__) . '/../_class/UserJSONObjectTranslatorImpl.php';
require_once dirname(__FILE__) . '/_stub/StubUserImpl.php';
require_once dirname(__FILE__) . '/_stub/StubUserLibraryImpl.php';
require_once dirname(__FILE__) . '/_stub/StubUserPrivilegesLibraryImpl.php';
require_once dirname(__FILE__).'/_stub/StubUserPrivilegesImpl.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 24/01/13
 * Time: 10:36
 * To change this template use File | Settings | File Templates.
 */
class UserJSONObjectTranslatorImplTest extends PHPUnit_Framework_TestCase
{
    /** @var UserJSONObjectTranslatorImpl */
    private $translator;
    /** @var StubUserImpl */
    private $user;
    /** @var StubUserLibraryImpl */
    private $userLibrary;
    /** @var StubUserPrivilegesLibraryImpl */
    private $userPrivilegesLibrary;

    public function setUp()
    {
        $this->userLibrary = new StubUserLibraryImpl();
        $this->userPrivilegesLibrary = new StubUserPrivilegesLibraryImpl();
        $this->userPrivilegesLibrary->setUserPrivileges(new StubUserPrivilegesImpl(false,true,true));
        $this->translator = new UserJSONObjectTranslatorImpl($this->userLibrary,$this->userPrivilegesLibrary);
        $this->user = new StubUserImpl();
    }

    public function testEncodeNonUserInstanceWillReturnFalse()
    {
        $this->assertFalse($this->translator->encode($this));
    }

    public function testEncodeWillReturnRightInstance(){
        $mail = 'some@some.dk';
        $this->user->setMail($mail);
        $username ='someUser';
        $this->user->setUsername($username);
        $parent = 'bob';
        $this->user->setParent($parent);
        $jsonObject = $this->translator->encode($this->user);
        $this->assertInstanceOf('UserJSONObjectImpl',$jsonObject);
        $this->assertEquals($mail,$jsonObject->getVariable('mail'));
        $this->assertEquals($username,$jsonObject->getVariable('username'));
        $this->assertEquals($parent,$jsonObject->getVariable('parent'));
        $this->assertEquals('site',$jsonObject->getVariable('privileges'));
    }

    public function testDecodeWillReturnFalseIfNotInstanceOfJSONObject(){
        $this->assertFalse($this->translator->decode($this));
    }

    public function testDecodeWillReturnFalseIfNotRightName(){
        $this->setUpUserLibrary();
        $jsonObject = new JSONObjectImpl('notUser');
        $jsonObject->setVariable('username','someUser');
        $jsonObject->setVariable('mail','someMail');
        $this->assertFalse($this->translator->decode($jsonObject));
    }

    public function testDecodeWillReturnFalseIfNotRightParameters(){
        $this->setUpUserLibrary();
        $jsonObject = new JSONObjectImpl('user');
        $jsonObject->setVariable('username','someUser');
        $this->assertFalse($this->translator->decode($jsonObject));
    }

    public function testDecodeWillReturnFalseIfNotInUserLibrary(){
        $jsonObject = new UserJSONObjectImpl('someUser','someMail','root');
        $this->assertFalse($this->translator->decode($jsonObject));
    }

    public function testDecodeWillReturnInstanceOfUserFromUserName(){
        $this->setUpUserLibrary();
        $jsonObject = new UserJSONObjectImpl('someUser','someMail','root');
        $user = $this->translator->decode($jsonObject);
        $this->assertTrue($user === $this->user);
    }

    public function setUpUserLibrary(){
        $userList = array($this->user);
        $this->userLibrary->setUserList($userList);
        $this->user->setUsername('someUser');
    }
}
