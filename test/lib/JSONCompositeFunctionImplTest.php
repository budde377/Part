<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 9:09 PM
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\json\TypeImpl;
use ChristianBudde\cbweb\controller\json\JSONFunctionImpl;
use ChristianBudde\cbweb\controller\json\CompositeFunctionImpl;
use PHPUnit_Framework_TestCase;

class JSONCompositeFunctionImplTest extends PHPUnit_Framework_TestCase
{

    private $function1Name;
    /** @var  TypeImpl */
    private $function1Target;
    /** @var  JSONFunctionImpl */
    private $function1;

    private $function2Name;
    /** @var  JSONFunctionImpl */
    private $function2;
    /** @var  CompositeFunctionImpl */
    private $compositeFunction;

    protected function setUp()
    {
        parent::setUp();
        $this->function1Name = "function1";
        $this->function1Target = new TypeImpl("SomeTarget");
        $this->function1 = new JSONFunctionImpl($this->function1Name, $this->function1Target);

        $this->function2Name = "function2";
        $this->function2 = new JSONFunctionImpl($this->function2Name, $this->function1);

        $this->compositeFunction = new CompositeFunctionImpl($this->function1Target);


    }


    public function testGetTargetGets()
    {
        $this->assertEquals($this->function1Target, $this->compositeFunction->getTarget());
    }


    public function testListFunctionsReturnArray()
    {
        $array = $this->compositeFunction->listFunctions();
        $this->assertTrue(is_array($array));
        $this->assertEquals(0, count($array));
    }


    public function testSetterSetsId()
    {
        $this->assertNull($this->compositeFunction->getId());
        $this->compositeFunction->setId($id = 123);
        $this->assertEquals($id, $this->compositeFunction->getId());
    }

    public function testSetIdWillSetIntVal()
    {
        $id = 'a';
        $this->compositeFunction->setID($id);
        $this->assertEquals(intval($id), $this->compositeFunction->getID());
    }


    public function testGetAsArrayReturnsRight()
    {
        $f = new CompositeFunctionImpl($this->compositeFunction->getTarget(), array_merge($this->compositeFunction->listFunctions(), [$this->function1, $this->function2]));
        $f->setId($id = 123);


        $array = $f->getAsArray();

        $this->assertTrue(is_array($array));
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('target', $array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('functions', $array);
        $this->assertEquals('composite_function', $array['type']);
        $this->assertEquals($id, $array['id']);
        $this->assertEquals($this->function1Target, $array['target']);
        $this->assertEquals([$this->function1, $this->function2], $array['functions']);


        $this->assertEquals($this->function1Target, $this->function1->getAsArray()['target']);

    }


}