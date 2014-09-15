<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 9:09 PM
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\json\JSONTypeImpl;
use ChristianBudde\cbweb\controller\json\JSONFunctionImpl;
use ChristianBudde\cbweb\controller\json\JSONCompositeFunctionImpl;
use PHPUnit_Framework_TestCase;

class JSONCompositeFunctionImplTest extends PHPUnit_Framework_TestCase
{

    private $function1Name;
    /** @var  \ChristianBudde\cbweb\ajax\json\\ChristianBudde\cbweb\controller\ajax\json\JSONTypeImpl */
    private $function1Target;
    /** @var  \ChristianBudde\cbweb\ajax\json\\ChristianBudde\cbweb\controller\ajax\json\JSONFunctionImpl */
    private $function1;

    private $function2Name;
    /** @var  \ChristianBudde\cbweb\ajax\json\\ChristianBudde\cbweb\controller\ajax\json\JSONFunctionImpl */
    private $function2;
    /** @var  \ChristianBudde\cbweb\ajax\json\\ChristianBudde\cbweb\controller\ajax\json\JSONCompositeFunctionImpl */
    private $compositeFunction;

    protected function setUp()
    {
        parent::setUp();
        $this->function1Name = "function1";
        $this->function1Target = new JSONTypeImpl("SomeTarget");
        $this->function1 = new JSONFunctionImpl($this->function1Name, $this->function1Target);

        $this->function2Name = "function2";
        $this->function2 = new JSONFunctionImpl($this->function2Name, $this->function1);

        $this->compositeFunction = new JSONCompositeFunctionImpl($this->function1Target);


    }


    public function testGetTargetGets()
    {
        $this->assertEquals($this->function1Target, $this->compositeFunction->getTarget());
    }

    public function testSetTargetSets()
    {
        $this->compositeFunction->setTarget($this->function2);
        $this->assertEquals($this->function2, $this->compositeFunction->getTarget());

    }

    public function testListFunctionsReturnArray()
    {
        $array = $this->compositeFunction->listFunctions();
        $this->assertTrue(is_array($array));
        $this->assertEquals(0, count($array));
    }

    public function testAddFunctionAdds()
    {
        $this->compositeFunction->appendFunction($this->function1);
        $this->assertEquals($this->function1, $this->compositeFunction->listFunctions()[0]);
    }

    public function testPrependFunctionPrepends()
    {
        $this->compositeFunction->prependFunction($this->function1);
        $this->compositeFunction->prependFunction($this->function2);
        $l = $this->compositeFunction->listFunctions();
        $this->assertEquals($this->function2, $l[0]);
        $this->assertEquals($this->function1, $l[1]);
    }

    public function testRemoveFunctionRemoves()
    {
        $this->compositeFunction->appendFunction($this->function1);
        $this->compositeFunction->removeFunction($this->function1);
        $this->assertEquals(0, count($this->compositeFunction->listFunctions()));
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

        $this->compositeFunction->setId($id = 123);
        $this->compositeFunction->appendFunction($this->function1);
        $this->compositeFunction->appendFunction($this->function2);

        $array = $this->compositeFunction->getAsArray();

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

    public function testGetAsJSONIsSimilarToArray()
    {
        $this->function2->setArg(0, "v0");
        $this->function2->setArg(2, "v2");
        $this->assertEquals(json_encode($this->compositeFunction->getAsArray()), $this->compositeFunction->getAsJSONString());
    }


    public function testCompositeFunctionSetsRootTarget()
    {
        $this->compositeFunction->setTarget($t1 = new JSONTypeImpl("newType"));
        $this->compositeFunction->appendFunction($this->function2);
        $this->assertEquals($t1, $this->function1->getTarget());
    }

    public function testCompositeFunctionSetsRootTargetOnPrepend()
    {
        $this->compositeFunction->setTarget($t1 = new JSONTypeImpl("newType"));
        $this->compositeFunction->prependFunction($this->function2);
        $this->assertEquals($t1, $this->function1->getTarget());
    }

    public function testCompositeFunctionUpdatesRootTarget()
    {
        $this->compositeFunction->appendFunction($this->function2);
        $this->compositeFunction->setTarget($t1 = new JSONTypeImpl("newType"));
        $this->assertEquals($t1, $this->function1->getTarget());
    }

}