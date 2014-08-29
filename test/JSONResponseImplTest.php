<?php
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

    public function setUp(){
        $this->response = new JSONResponseImpl($this->responseType,$this->responseErrorCode);
    }

    public function testGetTypeWillReturnType(){
        $this->assertEquals($this->responseType,$this->response->getResponseType());
    }

    public function testIdIsNullAsDefault(){
        $this->assertNull($this->response->getID());
    }

    public function testSetIdWillSetIntVal(){
        $id = 'a';
        $this->response->setID($id);
        $this->assertEquals(intval($id),$this->response->getID());
    }

    public function testSetIdWillSetId(){
        $id = 123;
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

    public function testSetPayloadCanBeArray(){
        $array = array(new NullJsonSerializableImpl());
        $this->response->setPayload($array);
        $this->assertTrue($this->response->getPayload() === $array);
    }


    public function testSetterWillSetArrayContainingJsonObjectSerializable(){
        $variableValue = new NullJSONObjectSerializableImpl();
        $this->response->setPayload($variableValue);
        $this->assertEquals($variableValue->jsonObjectSerialize(), $this->response->getPayload());
    }

    public function testSetterWillSetArrayContainingJsonObjectSerializableInArray(){
        $variableValue = new NullJSONObjectSerializableImpl();
        $this->response->setPayload([$variableValue]);
        $this->assertEquals([$variableValue->jsonObjectSerialize()], $this->response->getPayload());
    }

    public function testSetPayloadArrayMustContainScalar(){
        $array = array('test'=>$this);
        $this->response->setPayload($array);
        $this->assertNull($this->response->getPayload());
    }

    public function testGetErrorCodeWillReturnErrorCode(){
        $this->assertEquals($this->responseErrorCode,$this->response->getErrorCode());

    }

    public function testGetAsArrayWillReturnArray(){
        $this->response->setID(12333);
        $array = $this->response->getAsArray();
        $this->assertArrayHasKey('type',$array);
        $this->assertArrayHasKey('id',$array);
        $this->assertArrayHasKey('response_type',$array);
        $this->assertArrayHasKey('error_code',$array);
        $this->assertArrayHasKey('payload',$array);
        $this->assertEquals('response',$array['type']);
        $this->assertEquals($this->responseType,$array['response_type']);
        $this->assertEquals(12333,$array['id']);
        $this->assertEquals($this->responseErrorCode,$array['error_code']);
        $this->assertNull($array['payload']);
    }

    public function testGetAsArrayWillConsiderIndex(){
        $newResponse = new JSONResponseImpl();
        $object = new JSONObjectImpl("testName");
        $newResponse->setPayload($object);

        $array = $newResponse->getAsArray();
        $this->assertArrayHasKey('type',$array);
        $this->assertEquals('response',$array['type']);

        $this->assertArrayHasKey('response_type',$array);
        $this->assertArrayHasKey('error_code',$array);
        $this->assertArrayHasKey('payload',$array);
        $this->assertEquals($newResponse->getResponseType(),$array['response_type']);
        $this->assertNull($array['error_code']);
        $this->assertEquals($object, $array['payload']);
    }

    public function testGetAsJSONStringIsEquivalent(){
        $newRequest = new JSONResponseImpl();
        $object = new JSONObjectImpl("testName");
        $newRequest->setPayload($object);

        $array = json_decode($newRequest->getAsJSONString(),true);
        $this->assertArrayHasKey('type',$array);
        $this->assertEquals('response',$array['type']);
        $this->assertArrayHasKey('response_type',$array);
        $this->assertArrayHasKey('error_code',$array);
        $this->assertArrayHasKey('payload',$array);
        $this->assertNull($array['error_code']);

        $this->assertEquals($newRequest->getResponseType(),$array['response_type']);
        $this->assertTrue(is_array($array['payload']));
        $this->assertArrayHasKey('type',$array['payload']);
    }

    public function testGetAsArrayWillReturnPayloadArrayContainingObjectAsArray(){
        $object = new JSONObjectImpl('testName');
        $payload = array('test'=>$object);
        $this->response->setPayload($payload);
        $array = $this->response->getAsArray();
        $this->assertTrue(is_array($array['payload']));
        $this->assertEquals($object, $array['payload']['test']);
    }



}
