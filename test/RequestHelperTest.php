<?php
use ChristianBudde\cbweb\RequestHelper;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 7:50 PM
 * To change this template use File | Settings | File Templates.
 */
class RequestHelperTest extends PHPUnit_Framework_TestCase
{

    public function testURLFromGETMatchesGET()
    {
        $_GET = array('test' => '123');
        $url = '?test=123';
        $this->assertEquals($url, RequestHelper::URLFromGET(), "URL's did not match");
    }

    public function testURLWithoutVariableWillExcludeVariable()
    {
        $_GET = array('test' => '123', 'notGood' => 'fkkk');
        $url = '?test=123';
        $genUrl = RequestHelper::URLWithoutVariableFromGET('notGood');
        $this->assertEquals($url, $genUrl, "URLs did not match");
    }

    public function testGETIfSetElseDefaultWillReturnDefaultOnNotSet()
    {
        $_GET = array('test' => '123');
        $default = false;
        $this->assertEquals($default, RequestHelper::GETValueOfIndexIfSetElseDefault('notGood', $default), 'Defaults did not match');
        $this->assertEquals('123', RequestHelper::GETValueOfIndexIfSetElseDefault('test', $default), 'Value did not match');
    }

    public function testPOSTIfSetElseDefaultWillReturnDefaultOnNotSet()
    {
        $_POST = array('test' => '123');
        $default = false;
        $this->assertEquals($default, RequestHelper::POSTValueOfIndexIfSetElseDefault('notGood', $default), 'Defaults did not match');
        $this->assertEquals('123', RequestHelper::POSTValueOfIndexIfSetElseDefault('test', $default), 'Value did not match');
    }

    public function testCOOKIEIfSetElseDefaultWillReturnDefaultOnNotSet()
    {
        $_COOKIE = array('test' => '123');
        $default = false;
        $this->assertEquals($default, RequestHelper::COOKIEValueOfIndexIfSetElseDefault('notGood', $default), 'Defaults did not match');
        $this->assertEquals('123', RequestHelper::COOKIEValueOfIndexIfSetElseDefault('test', $default), 'Value did not match');
    }

    public function testSESSIONIfSetElseDefaultWillReturnDefaultOnNotSet()
    {
        $_SESSION = array('test' => '123');
        $default = false;
        $this->assertEquals($default, RequestHelper::SESSIONValueOfIndexIfSetElseDefault('notGood', $default), 'Defaults did not match');
        $this->assertEquals('123', RequestHelper::SESSIONValueOfIndexIfSetElseDefault('test', $default), 'Value did not match');
    }


}
