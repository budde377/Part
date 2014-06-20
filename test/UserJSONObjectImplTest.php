<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 24/01/13
 * Time: 10:28
 * To change this template use File | Settings | File Templates.
 */
class UserJSONObjectImplTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorWillSetVariables(){
        $jsonObject = new UserJSONObjectImpl('root','root@test.dk','root',400,'bob');
        $this->assertEquals('user',$jsonObject->getName());
        $this->assertEquals('root',$jsonObject->getVariable('username'));
        $this->assertEquals('root@test.dk',$jsonObject->getVariable('mail'));
        $this->assertEquals('bob',$jsonObject->getVariable('parent'));
        $this->assertEquals('root',$jsonObject->getVariable('privileges'));
        $this->assertEquals(400,$jsonObject->getVariable('last-login'));
    }



}
