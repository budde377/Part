<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/3/14
 * Time: 10:06 PM
 */

class UserPrivilegesJSONObjectImplTest extends  PHPUnit_Framework_TestCase{
    public function testConstructorWillSetVariables(){


        $privileges = new StubUserPrivilegesImpl(true, true, true);
        $privileges->pagePrivileges = ['page1','page2'];

        $jsonObject = new UserPrivilegesJSONObjectImpl($privileges);

        $this->assertEquals('user_privileges',$jsonObject->getName());
        $this->assertTrue($jsonObject->getVariable('root_privileges'));
        $this->assertTrue($jsonObject->getVariable('site_privileges'));
        $this->assertEquals($privileges->pagePrivileges, $jsonObject->getVariable('page_privileges'));


    }
} 