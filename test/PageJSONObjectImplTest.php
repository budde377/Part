<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 24/01/13
 * Time: 09:26
 * To change this template use File | Settings | File Templates.
 */
class PageJSONObjectImplTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorWillSetVariables(){

        $id = 'someId';
        $title = 'someTitle';
        $template = 'someTemplate';
        $alias = 'someAlias';
        $jsonObject = new PageJSONObjectImpl($id,$title,$template,$alias);

        $this->assertEquals('page',$jsonObject->getName());
        $this->assertEquals($id,$jsonObject->getVariable('id'));
        $this->assertEquals($title,$jsonObject->getVariable('title'));
        $this->assertEquals($template,$jsonObject->getVariable('template'));
        $this->assertEquals($alias,$jsonObject->getVariable('alias'));
    }

}
