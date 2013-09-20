<?php
require_once dirname(__FILE__).'/../_class/JSONFunctionImpl.php';
require_once dirname(__FILE__).'/../_class/JSONResponseImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 16:31
 * To change this template use File | Settings | File Templates.
 */
class JSONFunctionImplTest extends PHPUnit_Framework_TestCase
{
    private $functionName = "newFunction";
    /** @var JSONFunction */
    private $function;
    private $functionArgs = array('some','none');
    private $returnVal;

    public function setUp(){
        $this->returnVal = new JSONResponseImpl();
        $this->function = new JSONFunctionImpl($this->functionName,
            function(){return $this->returnVal;},
            $this->functionArgs);
    }

    public function testGetNameWillGetName(){
        $this->assertEquals($this->functionName,$this->function->getName());
    }

    public function testGetArgsWillReturnArgs(){
        $this->assertTrue(is_array($this->function->getArgs()));
        $this->assertEquals(0,count(array_diff($this->functionArgs,$this->function->getArgs())));
    }

    public function testGetArgsWillOnlyReturnArgsOfRightFormat(){
        $args = array($this,'some');
        $function = new JSONFunctionImpl($this->functionName,function(){return $this->returnVal;},$args);
        $newArgs = $function->getArgs();
        $this->assertEquals(1,count($newArgs));
        $this->assertEquals('some',$newArgs[0]);
    }

    public function testCallWillCallGivenFunction(){
        $this->assertEquals($this->returnVal,$this->function->call());
    }

    public function testCallWillCallByReference(){
        $function = new JSONFunctionImpl($this->functionName,function($a){return $a;});
        $callArgs = array("test");
        $this->assertEquals("test",$function->call($callArgs));
    }


}
