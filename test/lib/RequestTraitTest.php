<?php
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\util\traits\RequestTrait;
use PHPUnit_Framework_TestCase;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 7:50 PM
 * To change this template use File | Settings | File Templates.
 */
class RequestTraitTest extends PHPUnit_Framework_TestCase
{
    use RequestTrait;

    public function testURLFromGETMatchesGET()
    {
        $_GET = array('test' => '123');
        $url = '?test=123';
        $this->assertEquals($url, $this->URLFromGET(), "URL's did not match");
    }

    public function testURLWithoutVariableWillExcludeVariable()
    {
        $_GET = array('test' => '123', 'notGood' => 'fkkk');
        $url = '?test=123';
        $genUrl = $this->URLWithoutVariableFromGET('notGood');
        $this->assertEquals($url, $genUrl, "URLs did not match");
    }

    public function testGETIfSetElseDefaultWillReturnDefaultOnNotSet()
    {
        $_GET = array('test' => '123');
        $default = false;
        $this->assertEquals($default, $this->GETValueOfIndexIfSetElseDefault('notGood', $default), 'Defaults did not match');
        $this->assertEquals('123', $this->GETValueOfIndexIfSetElseDefault('test', $default), 'Value did not match');
    }

    public function testPOSTIfSetElseDefaultWillReturnDefaultOnNotSet()
    {
        $_POST = array('test' => '123');
        $default = false;
        $this->assertEquals($default, $this->POSTValueOfIndexIfSetElseDefault('notGood', $default), 'Defaults did not match');
        $this->assertEquals('123', $this->POSTValueOfIndexIfSetElseDefault('test', $default), 'Value did not match');
    }

    public function testCOOKIEIfSetElseDefaultWillReturnDefaultOnNotSet()
    {
        $_COOKIE = array('test' => '123');
        $default = false;
        $this->assertEquals($default, $this->COOKIEValueOfIndexIfSetElseDefault('notGood', $default), 'Defaults did not match');
        $this->assertEquals('123', $this->COOKIEValueOfIndexIfSetElseDefault('test', $default), 'Value did not match');
    }

    public function testSESSIONIfSetElseDefaultWillReturnDefaultOnNotSet()
    {
        $_SESSION = array('test' => '123');
        $default = false;
        $this->assertEquals($default, $this->SESSIONValueOfIndexIfSetElseDefault('notGood', $default), 'Defaults did not match');
        $this->assertEquals('123', $this->SESSIONValueOfIndexIfSetElseDefault('test', $default), 'Value did not match');
    }


}
