<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 12:36 PM
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\json\UserLibraryJSONObjectImpl;
use PHPUnit_Framework_TestCase;
use ChristianBudde\cbweb\test\stub\StubUserImpl;
use ChristianBudde\cbweb\test\stub\StubUserLibraryImpl;

class UserLibraryJSONObjectImplTest extends PHPUnit_Framework_TestCase
{

    public function testConstructorWillSetVariables()
    {


        $userLib = new StubUserLibraryImpl();
        $user1 = new StubUserImpl();
        $user1->setUsername("user1");
        $user2 = new StubUserImpl();
        $user2->setUsername("user2");
        $userLib->setUserList([$user1, $user2]);

        $object = new UserLibraryJSONObjectImpl($userLib);

        $this->assertEquals([$user1, $user2], $object->getVariable('users'));
        $this->assertEquals('user_library', $object->getName());

    }


}