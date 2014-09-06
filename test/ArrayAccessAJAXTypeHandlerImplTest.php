<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/6/14
 * Time: 6:08 PM
 */

class ArrayAccessAJAXTypeHandlerImplTest extends PHPUnit_Framework_TestCase{

    /** @var  ArrayAccessAJAXTypeHandlerImpl */
    private $handler;
    /** @var  FunctionStringParser */
    private $parser;
    /** @var  JSONFunction */
    private $function;

    protected function setUp()
    {
        parent::setUp();
        $this->handler = new ArrayAccessAJAXTypeHandlerImpl();
        $this->parser = new FunctionStringParserImpl();
        $this->function = $this->parser->parseFunctionString("POST.getVar('id')");
    }


    public function testHandlerCanHandleArray(){
        $this->handler->addArray('POST', $_POST);

        $this->assertTrue($this->handler->canHandle('POST', $this->function));
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString('POST.otherFunction()');
        $this->assertFalse($this->handler->canHandle('POST', $f));
        $this->assertFalse($this->handler->canHandle('SomeOtherType', $this->function));
    }

    public function testListTypesListsAddedArrays(){
        $this->handler->addArray("POST", $_POST);
        $this->handler->addArray("GET", $_GET);
        $this->handler->addArray("FILES", $_FILES);

        $this->assertEquals(["POST", "GET", "FILES"], $this->handler->listTypes());
    }

    public function testHandleReturnsEntryInArray(){
        $this->handler->addArray("POST", [1,2,3]);
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString('POST.getVar(0)');
        $this->assertEquals(1, $this->handler->handle('POST', $f));
        $f = $this->parser->parseFunctionString('POST.getVar(1)');
        $this->assertEquals(2, $this->handler->handle('POST', $f));
        $f = $this->parser->parseFunctionString('POST.getVar(2)');
        $this->assertEquals(3, $this->handler->handle('POST', $f));
    }

    public function testHasTypeWillReturnIfRight(){

        $this->handler->addArray("POST", [1,2,3]);
        $this->assertTrue($this->handler->hasType('POST'));
        $this->assertFalse($this->handler->hasType('NOT_POST'));
    }


} 