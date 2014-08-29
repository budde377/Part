<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 12:53 PM
 */

class JSONFunctionImplTest extends PHPUnit_Framework_TestCase{

    private $function1Name;
    /** @var  JSONTypeImpl */
    private $function1Target;
    /** @var  JSONFunctionImpl */
    private $function1;

    private $function2Name;
    /** @var  JSONFunctionImpl */
    private $function2;

    protected function setUp()
    {
        parent::setUp();
        $this->function1Name = "function1";
        $this->function1Target = new JSONTypeImpl("SomeTarget");
        $this->function1 = new JSONFunctionImpl($this->function1Target, $this->function1Name);

        $this->function2Name = "function2";
        $this->function2 = new JSONFunctionImpl($this->function1, $this->function2Name);

    }


    public function testGetTargetGets(){
        $this->assertEquals($this->function1Target, $this->function1->getTarget());
        $this->assertEquals($this->function1, $this->function2->getTarget());
    }

    public function testSetTargetSets(){
        $this->function1->setTarget($this->function2);
        $this->assertEquals($this->function2, $this->function1->getTarget());

    }


    public function testGetNameGets(){
        $this->assertEquals($this->function1Name, $this->function1->getName());
        $this->assertEquals($this->function2Name, $this->function2->getName());
    }

    public function testSetNameSets(){
        $name = "newName";
        $this->function1->setName($name);
        $this->assertEquals($name, $this->function1->getName());

    }

    public function testGetArgumentIsNullPrDefault(){
        $this->assertNull($this->function1->getArg(0));
    }

    public function testGetArgumentsIsEmptyArrayPrDefault(){
        $this->assertTrue(is_array($array = $this->function1->getArgs()));
        $this->assertEquals(0, count($array));
    }

    public function testSetterSetsId(){
        $this->assertNull($this->function1->getId());
        $this->function1->setId($id = 123);
        $this->assertEquals($id, $this->function1->getId());
    }

    public function testSetArgumentSetsArgument(){
        $val = "LOL";
        $this->function1->setArg(0, $val);
        $this->assertEquals($val, $this->function1->getArg(0));
    }

    public function testSetArgumentsFillsArrayUpToInserted(){
        $val1 = "v1";
        $val2 = "v2";
        $this->function1->setArg(0, $val1);
        $this->function1->setArg(4, $val2);
        $this->assertEquals(5, count($array = $this->function1->getArgs()));
        $this->assertTrue(isset($array[0]));
        $this->assertTrue(isset($array[4]));
        $this->assertEquals($val1, $array[0]);
        $this->assertNull($array[1]);
        $this->assertNull($array[2]);
        $this->assertNull($array[3]);
        $this->assertEquals($val2, $array[4]);
    }

    public function testWillNotSetArgumentWithNonNumericIndex(){
        $this->function1->setArg("test","test");
        $this->assertEquals(0, count($this->function1->getArgs()));
    }


    public function testWillNotSetNonScalarValue(){
        $this->function1->setArg(0, $this);
        $this->assertEquals(0, count($this->function1->getArgs()));
    }


    public function testSetterWillNotSetArrayWithNonScalar(){
        $this->function1->setArg(0,array($this));
        $this->assertNull($this->function1->getArg(0));
    }

    public function testSetterWillSetArrayContainingArraysOrScalars(){
        $variableValue = array("test", 'asd' => 'dsa', array(1, 2 , 3), new NullJsonSerializableImpl());
        $this->function1->setArg(0,$variableValue);
        $this->assertEquals($variableValue, $this->function1->getArg(0));
    }


    public function testSetterWillSetArrayContainingJsonObjectSerializable(){
        $variableValue = new NullJSONObjectSerializableImpl();
        $this->function1->setArg(0,$variableValue);
        $this->assertEquals($variableValue->jsonObjectSerialize(), $this->function1->getArg(0));
    }

    public function testSetterWillSetArrayContainingJsonObjectSerializableInArray(){
        $variableValue = new NullJSONObjectSerializableImpl();
        $this->function1->setArg(0, [[$variableValue]]);
        $this->assertEquals([[$variableValue->jsonObjectSerialize()]], $this->function1->getArg(0));
    }

    public function testWillSetJSONElement(){
        $this->function1->setArg(0, $this->function2);
        $this->assertEquals(1, count($this->function1->getArgs()));
        $this->assertEquals($this->function2, $this->function1->getArg(0));
    }


    public function testGetAsArrayReturnsRight(){
        $this->function2->setArg(0, "v0");
        $this->function2->setArg(2, "v2");
        $this->function2->setId($id = 123);
        $array = $this->function2->getAsArray();

        $this->assertTrue(is_array($array));
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('target', $array);
        $this->assertArrayHasKey('arguments', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('id', $array);
        $this->assertEquals('function', $array['type']);
        $this->assertEquals($this->function2Name, $array['name']);
        $this->assertEquals($id, $array['id']);
        $this->assertEquals($this->function1->getAsArray(), $array['target']);
        $this->assertEquals(array('v0', null,'v2'), $array['arguments']);


        $this->assertEquals($this->function1Target->getAsArray(), $this->function1->getAsArray()['target']);

    }

    public function testGetAsJSONIsSimilarToArray(){
        $this->function2->setArg(0, "v0");
        $this->function2->setArg(2, "v2");
        $this->assertEquals(json_encode($this->function2->getAsArray()), $this->function2->getAsJSONString());
    }


    public function testClearArgumentsClears(){
        $this->function1->setArg(0, "v0");
        $this->function1->setArg(1, "v1");
        $this->function1->clearArguments();
        $this->assertEquals(array(), $this->function1->getArgs());
    }

    public function testSetArgumentAfterClearIsOk(){
        $this->function1->setArg(4, "v4");
        $this->function1->clearArguments();
        $this->function1->setArg(3, "v3");
        $this->assertEquals(4, count($this->function1->getArgs()));
    }


    public function testSetArgumentToObjectIsOk(){
        $this->function1->setArg(0, $obj = new JSONObjectImpl("obj1"));
        $this->assertTrue(strpos($this->function1->getAsJSONString(), $obj->getAsJSONString()) !== false);
    }

}