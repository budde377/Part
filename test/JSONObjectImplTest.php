<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 14:58
 * To change this template use File | Settings | File Templates.
 */
class JSONObjectImplTest extends PHPUnit_Framework_TestCase
{
    private $objectName = "SomeObject";
    /** @var JSONObject */
    private $object;

    protected function setUp()
    {
        $this->object = new JSONObjectImpl($this->objectName);
    }

    public function testGetNameWillReturnName(){
        $this->assertEquals($this->objectName,$this->object->getName());
    }

    public function testSetVariableWillSetVariable(){
        $variableName = "testVariable";
        $variableValue = "test";
        $this->object->setVariable($variableName,$variableValue);
        $this->assertEquals($variableValue,$this->object->getVariable($variableName));

    }

    public function testGetNotSetVariableWillReturnNull(){
        $this->assertNull($this->object->getVariable("notSetVariable"));
    }

    public function testSetterCanSetName(){
        $variableName = "name";
        $variableValue = "someName";
        $this->object->setVariable($variableName,$variableValue);
        $this->assertEquals($variableValue,$this->object->getVariable($variableName));
    }

    public function testSetterWillNotSetNonScalar(){
        $variableName = "testVariable";
        $this->object->setVariable($variableName,$this);
        $this->assertNull($this->object->getVariable($variableName));
    }

    public function testSettersWillSetInstanceOfJSONObject(){
        $variableName = "testVariable";
        $this->object->setVariable($variableName,$this->object);
        $this->assertTrue($this->object === $this->object->getVariable($variableName));
    }

    public function testGetAsArrayWillReturnCorrectArray(){
        $newObject = new JSONObjectImpl('newObject');
        $this->object->setVariable('object',$newObject);
        $this->object->setVariable('string','test');

        $array = $this->object->getAsArray();

        $this->assertTrue(is_array($array));
        $this->assertArrayHasKey('type',$array);
        $this->assertArrayHasKey('name',$array);
        $this->assertArrayHasKey('variables',$array);
        $this->assertTrue(is_array($array['variables']));
        $this->assertArrayHasKey('string',$array['variables']);
        $this->assertArrayHasKey('object',$array['variables']);

        $this->assertEquals('object',$array['type']);
        $this->assertEquals($this->objectName,$array['name']);
        $this->assertEquals('test',$array['variables']['string']);
        $this->assertTrue(is_array($array['variables']['object']));

        $this->assertArrayHasKey('type',$array['variables']['object']);
        $this->assertArrayHasKey('name',$array['variables']['object']);
        $this->assertArrayHasKey('variables',$array['variables']['object']);
        $this->assertTrue(is_array($array['variables']['object']['variables']));
        $this->assertEquals('newObject',$array['variables']['object']['name']);
        $this->assertEquals('object',$array['variables']['object']['type']);
        $this->assertEquals(0,count($array['variables']['object']['variables']));
    }

    public function testGetAsJSONStringWillBeLikeArray(){
        $newObject = new JSONObjectImpl('newObject');
        $this->object->setVariable('object',$newObject);
        $this->object->setVariable('string','test');

        $array = json_decode($this->object->getAsJSONString(),true);

        $this->assertTrue(is_array($array));
        $this->assertArrayHasKey('type',$array);
        $this->assertArrayHasKey('name',$array);
        $this->assertArrayHasKey('variables',$array);
        $this->assertTrue(is_array($array['variables']));
        $this->assertArrayHasKey('string',$array['variables']);
        $this->assertArrayHasKey('object',$array['variables']);

        $this->assertEquals('object',$array['type']);
        $this->assertEquals($this->objectName,$array['name']);
        $this->assertEquals('test',$array['variables']['string']);
        $this->assertTrue(is_array($array['variables']['object']));

        $this->assertArrayHasKey('type',$array['variables']['object']);
        $this->assertArrayHasKey('name',$array['variables']['object']);
        $this->assertArrayHasKey('variables',$array['variables']['object']);
        $this->assertTrue(is_array($array['variables']['object']['variables']));
        $this->assertEquals('newObject',$array['variables']['object']['name']);
        $this->assertEquals('object',$array['variables']['object']['type']);
        $this->assertEquals(0,count($array['variables']['object']['variables']));
    }



}
