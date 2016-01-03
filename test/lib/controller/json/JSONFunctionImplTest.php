<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 12:53 PM
 */
namespace ChristianBudde\Part\controller\json;



use PHPUnit_Framework_TestCase;

class JSONFunctionImplTest extends PHPUnit_Framework_TestCase
{

    private $function1Name;
    /** @var  TypeImpl */
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
        $this->function1Target = new TypeImpl("SomeTarget");
        $this->function1 = new JSONFunctionImpl($this->function1Name, $this->function1Target);

        $this->function2Name = "function2";
        $this->function2 = new JSONFunctionImpl($this->function2Name, $this->function1);

    }


    public function testGetTargetGets()
    {
        $this->assertEquals($this->function1Target, $this->function1->getTarget());
        $this->assertEquals($this->function1, $this->function2->getTarget());
    }



    public function testGetNameGets()
    {
        $this->assertEquals($this->function1Name, $this->function1->getName());
        $this->assertEquals($this->function2Name, $this->function2->getName());
    }


    public function testGetArgumentIsNullPrDefault()
    {
        $this->assertNull($this->function1->getArg(0));
    }

    public function testGetArgumentsIsEmptyArrayPrDefault()
    {
        $this->assertTrue(is_array($array = $this->function1->getArgs()));
        $this->assertEquals(0, count($array));
    }

    public function testSetterSetsId()
    {
        $this->assertNull($this->function1->getId());
        $this->function1->setId($id = 123);
        $this->assertEquals($id, $this->function1->getId());
    }


    public function testSetIdWillSetIntVal()
    {
        $id = 'a';
        $this->function1->setID($id);
        $this->assertEquals(intval($id), $this->function1->getID());
    }


    public function testGetAsArrayReturnsRight()
    {


        $this->function2 = new JSONFunctionImpl($this->function2->getName(), $this->function2->getTarget(), ["v0", null, "v2"]);
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
        $this->assertEquals($this->function1, $array['target']);
        $this->assertEquals(array('v0', null, 'v2'), $array['arguments']);


        $this->assertEquals($this->function1Target, $this->function1->getAsArray()['target']);

    }





}