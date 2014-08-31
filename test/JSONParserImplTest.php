<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 2:00 PM
 */

class JSONParserImplTest extends PHPUnit_Framework_TestCase{

    /** @var  JSONParserImpl */
    private $parser;

    private $function1Name = "function1";
    /** @var  JSONTypeImpl */
    private $function1Target;
    /** @var  JSONFunctionImpl */
    private $function1;

    private $function2Name = "function2";
    /** @var  JSONFunctionImpl */
    private $function2;


    private $objectName = "SomeObject";
    /** @var JSONObject */
    private $object1;
    private $object2;


    private $typeString = "someType";
    /** @var  JSONTypeImpl */
    private $type;
    /** @var  JSONResponseImpl  */
    private $response;
    private $responseType = JSONResponse::RESPONSE_TYPE_ERROR;
    private $responseErrorCode = JSONResponse::ERROR_CODE_MALFORMED_REQUEST;

    public function setUp(){

        $this->parser = new JSONParserImpl();
        $this->function1Target = new JSONTypeImpl("SomeTarget");
        $this->function1 = new JSONFunctionImpl($this->function1Name, $this->function1Target);

        $this->function2 = new JSONFunctionImpl($this->function2Name, $this->function1);
        $this->function2->setId(123);
        $this->function2->setArg(3, "v3");

        $this->object1 = new JSONObjectImpl($this->objectName);
        $this->object2 = new JSONObjectImpl($this->objectName);

        $this->function1->setArg(4, $this->object1);


        $this->object1->setVariable("key0", "val0");
        $this->object1->setVariable("key1", $this->object2);
        $this->type = new JSONTypeImpl($this->typeString);
        $this->response = new JSONResponseImpl($this->responseType,$this->responseErrorCode);
    }


    public function testParserParsesObject(){
        /** @var JSONObject $obj */
        $obj = $this->parser->parse($this->object1->getAsJSONString());
        $this->assertInstanceOf('JSONObject', $obj);
        $this->assertEquals($obj->getAsJSONString(), $this->object1->getAsJSONString());
        $this->assertEquals($this->object2, $obj->getVariable('key1'));
    }

    public function testParserParsesFunction(){
        /** @var JSONFunction $obj */
        $obj = $this->parser->parse($this->function1->getAsJSONString());
        $this->assertInstanceOf('JSONFunction', $obj);
        $this->assertEquals($this->object1, $obj->getArg(4));
        $this->assertEquals($obj->getAsJSONString(), $this->function1->getAsJSONString());
        $this->assertInstanceOf('JSONTarget', $obj->getTarget());
        $this->assertInstanceOf('JSONType', $obj->getTarget());
    }
    public function testParserParsesFunction2(){
        /** @var JSONFunction $obj */
        $obj = $this->parser->parse($this->function2->getAsJSONString());
        $this->assertInstanceOf('JSONFunction', $obj);
        $this->assertEquals($obj->getAsJSONString(), $this->function2->getAsJSONString());
        $this->assertInstanceOf('JSONTarget', $obj->getTarget());
        $this->assertInstanceOf('JSONFunction', $obj->getTarget());

    }
    public function testParserParsesResponse(){
        /** @var JSONResponse $obj */
        $this->response->setPayload($this->object1);
        $obj = $this->parser->parse($this->response->getAsJSONString());
        $this->assertInstanceOf('JSONResponse', $obj);
        $this->assertEquals($obj->getAsJSONString(), $this->response->getAsJSONString());
        $this->assertInstanceOf('JSONObject', $obj->getPayload());

    }

    public function testParserParsesResponseWithOOutPayload(){
        /** @var JSONResponse $obj */
        $obj = $this->parser->parse($this->response->getAsJSONString());
        $this->assertInstanceOf('JSONResponse', $obj);
        $this->assertEquals($obj->getAsJSONString(), $this->response->getAsJSONString());

    }

    public function testsParserParsesType(){
        $obj = $this->parser->parse($this->type->getAsJSONString());
        $this->assertInstanceOf('JSONType', $obj);
        $this->assertEquals($obj->getAsJSONString(), $this->type->getAsJSONString());
    }

    public function testArrayWithTypeIsParsedCorrectly(){
        $array = array('type' => $this->function1);
        $result  = $this->parser->parse(json_encode($array));
        $this->assertEquals($array, $result);
    }

    public function testArrayWithMissingEntriesIsParsedCorrectlyOnFunction(){
        $array = array('type' => 'function');
        $result  = $this->parser->parse(json_encode($array));
        $this->assertEquals($array, $result);
    }

    public function testArrayWithMissingEntriesIsParsedCorrectlyOnType(){
        $array = array('type' => 'type');
        $result  = $this->parser->parse(json_encode($array));
        $this->assertEquals($array, $result);
    }

    public function testArrayWithMissingEntriesIsParsedCorrectlyOnObject(){
        $array = array('type' => 'object');
        $result  = $this->parser->parse(json_encode($array));
        $this->assertEquals($array, $result);
    }

    public function testArrayWithMissingEntriesIsParsedCorrectlyOnResponse(){
        $array = array('type' => 'response');
        $result  = $this->parser->parse(json_encode($array));
        $this->assertEquals($array, $result);
    }

}