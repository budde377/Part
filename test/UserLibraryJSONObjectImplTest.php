<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 12:36 PM
 */

class UserLibraryJSONObjectImplTest extends  PHPUnit_Framework_TestCase{

    public function testConstructorWillSetVariables(){


        $userLib = new StubUserLibraryImpl();
        $user1 = new StubUserImpl();
        $user1->setUsername("user1");
        $user2 = new StubUserImpl();
        $user2->setUsername("user2");
        $userLib->setUserList([$user1, $user2]);

        $object = new UserLibraryJSONObjectImpl($userLib);

        $this->assertEquals([$user1->jsonObjectSerialize(), $user2->jsonObjectSerialize()], $object->getVariable('users'));
        $this->assertEquals('user_library', $object->getName());

    }


} 