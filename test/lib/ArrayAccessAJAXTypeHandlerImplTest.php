<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/6/14
 * Time: 6:08 PM
 */
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\controller\ajax\type_handler\ArrayAccessTypeHandlerImpl;
use ChristianBudde\Part\controller\function_string\ParserImpl;
use ChristianBudde\Part\controller\json\JSONFunction;
use PHPUnit_Framework_TestCase;

class ArrayAccessAJAXTypeHandlerImplTest extends PHPUnit_Framework_TestCase
{

    /** @var  \ChristianBudde\Part\controller\ajax\type_handler\ArrayAccessTypeHandlerImpl */
    private $handler;
    private $parser;
    /** @var  JSONFunction */
    private $function;

    protected function setUp()
    {
        parent::setUp();
        $this->handler = new ArrayAccessTypeHandlerImpl();
        $p = $this->parser = function ($string){
            $p = new ParserImpl();
            return $p->parseString($string)->toJSONProgram();
        };
        $this->function = $p("POST.arrayAccess('id')");
    }


    public function testHandlerCanHandleArray()
    {
        $this->handler->addArray('POST', $_POST);

        $this->assertTrue($this->handler->canHandle('POST', $this->function));
        /** @var JSONFunction $f */
        $f = call_user_func($this->parser, ('POST.otherFunction()'));
        $this->assertFalse($this->handler->canHandle('POST', $f));
        $this->assertFalse($this->handler->canHandle('SomeOtherType', $this->function));
    }

    public function testListTypesListsAddedArrays()
    {
        $this->handler->addArray("POST", $_POST);
        $this->handler->addArray("GET", $_GET);
        $this->handler->addArray("FILES", $_FILES);

        $this->assertEquals(["array", "POST", "GET", "FILES"], $this->handler->listTypes());
    }

    public function testHandleReturnsEntryInArray()
    {
        $a = [1,2,3];
        $this->handler->addArray("POST", $a);
        /** @var JSONFunction $f */
        $f = call_user_func($this->parser, 'POST.getVar(0)');
        $this->assertEquals(1, $this->handler->handle('POST', $f));
        $f = call_user_func($this->parser, 'POST.getVar(1)');
        $this->assertEquals(2, $this->handler->handle('POST', $f));
        $f = call_user_func($this->parser, 'POST.getVar(2)');
        $this->assertEquals(3, $this->handler->handle('POST', $f));
    }

    public function testHasTypeWillReturnIfRight()
    {
        $a = [1, 2, 3];
        $this->handler->addArray("POST", $a);
        $this->assertTrue($this->handler->hasType('POST'));
        $this->assertTrue($this->handler->hasType('array'));
        $this->assertFalse($this->handler->hasType('NOT_POST'));
    }

    public function testCanHandleAccessOnArrayIfGivenInstance()
    {
        $t = $this->handler->canHandle('array', $this->function, $array = ["id" => 1, 2, 3]);
        $this->assertTrue($t);
        $this->assertFalse($this->handler->canHandle('array', $this->function));
        $this->assertEquals(1, $this->handler->handle('array', $this->function, $array));
    }


    public function testArrayPassByReference()
    {
        $array = [];
        $this->handler->addArray('POST', $array);
        $t = $this->handler->canHandle('POST', $this->function);
        $this->assertTrue($t);
        $array['id'] = 2;
        $this->assertEquals(2, $this->handler->handle('POST', $this->function));

    }

}