<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/3/14
 * Time: 10:06 PM
 */
namespace ChristianBudde\Part\controller\ajax\type_handler;

use ChristianBudde\Part\controller\json\UserPrivilegesObjectImpl;
use ChristianBudde\Part\model\user\StubUserPrivilegesImpl;
use PHPUnit_Framework_TestCase;

class UserPrivilegesObjectImplTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorWillSetVariables()
    {


        $privileges = new StubUserPrivilegesImpl(true, true, true);
        $privileges->pagePrivileges = ['page1', 'page2'];

        $jsonObject = new UserPrivilegesObjectImpl($privileges);

        $this->assertEquals('user_privileges', $jsonObject->getName());
        $this->assertTrue($jsonObject->getVariable('root_privileges'));
        $this->assertTrue($jsonObject->getVariable('site_privileges'));
        $this->assertEquals($privileges->pagePrivileges, $jsonObject->getVariable('page_privileges'));


    }
    public function testConstructorWillSetVariables2()
    {


        $privileges = new StubUserPrivilegesImpl(true, false, true);
        $privileges->pagePrivileges = ['page1', 'page2'];

        $jsonObject = new UserPrivilegesObjectImpl($privileges);

        $this->assertEquals('user_privileges', $jsonObject->getName());
        $this->assertTrue($jsonObject->getVariable('root_privileges'));
        $this->assertFalse($jsonObject->getVariable('site_privileges'));
        $this->assertEquals($privileges->pagePrivileges, $jsonObject->getVariable('page_privileges'));


    }
}