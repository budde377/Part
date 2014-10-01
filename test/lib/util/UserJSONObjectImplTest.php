<?php
namespace ChristianBudde\cbweb\test\util;

use ChristianBudde\cbweb\controller\json\UserObjectImpl;
use PHPUnit_Framework_TestCase;
use ChristianBudde\cbweb\test\stub\StubUserImpl;
use ChristianBudde\cbweb\test\stub\StubUserPrivilegesImpl;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 24/01/13
 * Time: 10:28
 * To change this template use File | Settings | File Templates.
 */
class UserJSONObjectImplTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorWillSetVariables()
    {
        $userName = 'root';
        $mail = 'mail';
        $parent = 'parent';
        $privileges = new StubUserPrivilegesImpl(true, false, false);
        $lastLogin = 1337;


        $user = new StubUserImpl();
        $user->setUsername($userName);
        $user->setMail($mail);
        $user->setParent($parent);
        $user->setUserPrivileges($privileges);
        $user->lastLogin = $lastLogin;

        $jsonObject = new UserObjectImpl($user);

        $this->assertEquals('user', $jsonObject->getName());
        $this->assertEquals($userName, $jsonObject->getVariable('username'));
        $this->assertEquals($mail, $jsonObject->getVariable('mail'));
        $this->assertEquals($parent, $jsonObject->getVariable('parent'));
        $this->assertEquals($privileges, $jsonObject->getVariable('privileges'));
        $this->assertEquals($lastLogin, $jsonObject->getVariable('last-login'));
    }


}
