<?php
require_once dirname(__FILE__).'/../_class/JSONResponseImpl.php';
require_once dirname(__FILE__).'/../_class/JSONObjectImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 15:33
 * To change this template use File | Settings | File Templates.
 */
class JSONResponseImplTest extends PHPUnit_Framework_TestCase
{
    /** @var JSONResponseImpl */
    private $response;
    private $responseType = JSONResponse::RESPONSE_TYPE_ERROR;
    private $responseErrorCode = JSONResponse::ERROR_CODE_MALFORMED_REQUEST;
    private $errorMessage = "Some message";

    public function setUp(){
        $this->response = new JSONResponseImpl($this->responseType,$this->responseErrorCode,$this->errorMessage);
    }

    public function testGetTypeWillReturnType(){
        $this->assertEquals($this->responseType,$this->response->getType());
    }

    public function testIdIsNullAsDefault(){
        $this->assertNull($this->response->getID());
    }

    public function testSetIdWillSetId(){
        $id = "someID";
        $this->response->setID($id);
        $this->assertEquals($id,$this->response->getID());
    }

    public function testSetPayloadWillSetPayload(){
        $payload = "payload";
        $this->response->setPayload($payload);
        $this->assertEquals($payload,$this->response->getPayload());
    }

    public function testSetPayloadMustBeScalar(){
        $this->response->setPayload($this);
        $this->assertNull($this->response->getPayload());
    }

    public function testSetPayloadCanBeJSONObject(){
        $object = new JSONObjectImpl("testName");
        $this->response->setPayload($object);
        $this->assertTrue($object == $this->response->getPayload());
    }

    public function testGetErrorCodeWillReturnErrorCode(){
        $this->assertEquals($this->responseErrorCode,$this->response->getErrorCode());

    }

    public function testGetErrorMessageWillReturnErrorMessage(){
        $this->assertEquals($this->errorMessage,$this->response->getErrorMessage());
    }

    public function testGetAsArrayWillReturnArray(){
        $array = $this->response->getAsArray();
        $this->assertArrayHasKey('type',$array);
        $this->assertArrayHasKey('error_message',$array);
        $this->assertArrayHasKey('error_code',$array);
        $this->assertArrayNotHasKey('payload',$array);
        $this->assertEquals($this->responseType,$array['type']);
        $this->assertEquals($this->errorMessage,$array['error_message']);
        $this->assertEquals($this->responseErrorCode,$array['error_code']);
    }

    public function testGetAsArrayWillConsiderIndex(){
        $newRequest = new JSONResponseImpl();
        $object = new JSONObjectImpl("testName");
        $newRequest->setPayload($object);

        $array = $newRequest->getAsArray();
        $this->assertArrayHasKey('type',$array);
        $this->assertArrayNotHasKey('error_message',$array);
        $this->assertArrayNotHasKey('error_code',$array);
        $this->assertArrayHasKey('payload',$array);
        $this->assertEquals($newRequest->getType(),$array['type']);
        $this->assertTrue(is_array($array['payload']));
        $this->assertArrayHasKey('type',$array['payload']);
    }

    public function testGetAsJSONStringIsEquivalent(){
        $newRequest = new JSONResponseImpl();
        $object = new JSONObjectImpl("testName");
        $newRequest->setPayload($object);

        $array = json_decode($newRequest->getAsJSONString(),true);
        $this->assertArrayHasKey('type',$array);
        $this->assertArrayNotHasKey('error_message',$array);
        $this->assertArrayNotHasKey('error_code',$array);
        $this->assertArrayHasKey('payload',$array);
        $this->assertEquals($newRequest->getType(),$array['type']);
        $this->assertTrue(is_array($array['payload']));
        $this->assertArrayHasKey('type',$array['payload']);
    }

}
