<?php
require_once dirname(__FILE__).'/../_class/JSONServerImpl.php';
require_once dirname(__FILE__).'/../_class/JSONFunctionImpl.php';
require_once dirname(__FILE__).'/../_class/JSONResponseImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 23/01/13
 * Time: 10:18
 * To change this template use File | Settings | File Templates.
 */
class JSONServerImplTest extends PHPUnit_Framework_TestCase
{
    /** @var JSONServerImpl */
    private $server;
    /** @var JSONFunction */
    private $function;
    /** @var JSONResponse */
    private $functionReturn;

    public function setUp(){
        $this->server = new JSONServerImpl();
        $this->function = new JSONFunctionImpl('testFunction',function(){return $this->functionReturn;});
        $this->functionReturn = new JSONResponseImpl();

    }

    public function testEvaluateWillReturnResponseOnEmptyString(){
        $jsonString = "";
        $response = $this->server->evaluate($jsonString);
        $this->assertInstanceOf('JSONResponse',$response);
        $this->assertEquals(JSONResponse::RESPONSE_TYPE_ERROR,$response->getResponseType());
        $this->assertEquals(JSONResponse::ERROR_CODE_MALFORMED_REQUEST,$response->getErrorCode());
    }


    public function testEvaluateMalformedRequestWillReturnResponseWithID(){
        $id = 123;
        $jsonString = json_encode(array('type'=>'something','id'=>$id));
        $response = $this->server->evaluate($jsonString);
        $this->assertInstanceOf('JSONResponse',$response);
        $this->assertEquals(JSONResponse::RESPONSE_TYPE_ERROR,$response->getResponseType());
        $this->assertEquals(JSONResponse::ERROR_CODE_MALFORMED_REQUEST,$response->getErrorCode());
        $this->assertEquals($id,$response->getID());
    }


    public function testEvaluateWillReturnRightErrorResponseOnFunctionNotFound(){
        $id = 123;
        $jsonString = json_encode(array('type'=>'function','name'=>'nonExistingFunction','args'=>array(),'id'=>$id));
        $response = $this->server->evaluate($jsonString);
        $this->assertInstanceOf('JSONResponse',$response);
        $this->assertEquals(JSONResponse::RESPONSE_TYPE_ERROR,$response->getResponseType());
        $this->assertEquals(JSONResponse::ERROR_CODE_NO_SUCH_FUNCTION,$response->getErrorCode());
        $this->assertEquals($id,$response->getID());
    }

    public function testEvaluateWillReturnResultFromFunctionOnFoundFunction(){
        $jsonString = json_encode(array('type'=>'function','name'=>'testFunction','args'=>array()));
        $this->server->registerJSONFunction($this->function);
        $response = $this->server->evaluate($jsonString);
        $this->assertTrue($response === $this->functionReturn);
    }

    public function testEvaluateWillReturnErrorOnWrongArguments(){
        $id = 123;
        $jsonString = json_encode(array('type'=>'function','name'=>'testFunction','args'=>array(),'id'=>$id));
        $function = new JSONFunctionImpl('testFunction',function(){return $this->functionReturn;},array('name','test'));
        $this->server->registerJSONFunction($function);
        $response = $this->server->evaluate($jsonString);
        $this->assertInstanceOf('JSONResponse',$response);
        $this->assertEquals(JSONResponse::RESPONSE_TYPE_ERROR,$response->getResponseType());
        $this->assertEquals(JSONResponse::ERROR_CODE_MISSING_ARGUMENT,$response->getErrorCode());
        $this->assertEquals($id,$response->getID());
     }

    public function testEvaluateWillParseArguments(){
        $val ='someVal';
        $jsonString = json_encode(array('type'=>'function','name'=>'testFunction','args'=>array('test'=>$val)));
        $callFunction = function($test){if($test == 'someVal'){return null;}return $this->functionReturn;};
        $function = new JSONFunctionImpl('testFunction',$callFunction,array('test'));
        $this->server->registerJSONFunction($function);
        $response = $this->server->evaluate($jsonString);
        $this->assertNull($response);
    }
    public function testEvaluateWillFigureArgumentOrder(){
        $jsonString = json_encode(array('type'=>'function','name'=>'testFunction','args'=>array('test2'=>false,'test'=>true)));
        $callFunction = function($test,$test2){
            if($test){
                return null;
            }
            return $this->functionReturn;
        };
        $function = new JSONFunctionImpl('testFunction',$callFunction,array('test','test2'));
        $this->server->registerJSONFunction($function);
        $response = $this->server->evaluate($jsonString);
        $this->assertNull($response);
    }

    public function testEvaluateWillAddID(){
        $id = 1233;
        $jsonString = json_encode(array('type'=>'function','name'=>'testFunction','args'=>array(),'id'=>$id));
        $callFunction = function(){return $this->functionReturn;};
        $function = new JSONFunctionImpl('testFunction',$callFunction);
        $this->server->registerJSONFunction($function);
        $response = $this->server->evaluate($jsonString);
        $this->assertEquals($id,$response->getID());
    }


}
