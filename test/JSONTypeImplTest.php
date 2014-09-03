<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 1:01 PM
 */

class JSONTypeImplTest extends PHPUnit_Framework_TestCase{

    private $typeString;
    /** @var  JSONTypeImpl */
    private $type;


    protected function setUp()
    {
        $this->typeString = "someType";
        $this->type = new JSONTypeImpl($this->typeString);


    }


    public function testJSONTypeReturnsTypeGivenInConstructor(){
        $this->assertEquals($this->typeString, $this->type->getTypeString());
    }

    public function testGetArrayIsRight(){
        $array = $this->type->getAsArray();
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('type_string', $array);

        $this->assertEquals('type', $array['type']);
        $this->assertEquals($this->typeString, $array['type_string']);

    }

    public function testJSONIsSimilarToArray(){
        $this->assertEquals(json_encode($this->type->getAsArray()), $this->type->getAsJSONString());
    }

} 