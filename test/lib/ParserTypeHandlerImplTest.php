<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 6:39 PM
 */

namespace ChristianBudde\Part\test;


use ChristianBudde\Part\controller\ajax\ParserTypeHandlerImpl;
use ChristianBudde\Part\controller\json\JSONFunctionImpl;
use ChristianBudde\Part\controller\json\TypeImpl;

class ParserTypeHandlerImplTest extends \PHPUnit_Framework_TestCase{
    /** @var  ParserTypeHandlerImpl */
    private $handler;

    protected function setUp()
    {
        $this->handler = new ParserTypeHandlerImpl();

    }


    public function testHasParserType()
    {
        $this->assertEquals(['Parser'], $this->handler->listTypes());
    }


    public function testHasTypeParserType(){
        $this->assertTrue($this->handler->hasType('Parser'));
    }


    public function testCantHandleOtherType(){
        $this->assertFalse($this->handler->canHandle('NotParser', new JSONFunctionImpl('parseJson', new TypeImpl('NotParser'))));
    }


    public function testCanHandleParserParse(){
        $this->assertTrue($this->handler->canHandle('Parser', new JSONFunctionImpl('parseJson', new TypeImpl('Parser'), ["{}"])));
        $this->assertTrue($this->handler->canHandle('Parser', new JSONFunctionImpl('parseFunctionStringArray', new TypeImpl('Parser'), ["[]"])));
    }

    public function testCantHandleLessArguments(){
        $this->assertFalse($this->handler->canHandle('Parser', new JSONFunctionImpl('parseJson', new TypeImpl('Parser'))));
        $this->assertFalse($this->handler->canHandle('Parser', new JSONFunctionImpl('parseFunctionStringArray', new TypeImpl('Parser'))));
    }


    public function testCantHandleNonString(){
        $this->assertFalse($this->handler->canHandle('Parser', new JSONFunctionImpl('parseJson', new TypeImpl('Parser'),[[]])));
        $this->assertFalse($this->handler->canHandle('Parser', new JSONFunctionImpl('parseFunctionStringArray', new TypeImpl('Parser'),[[]])));
    }


    public function testHandleParses(){
        $this->assertEquals([1,2,3], $this->handler->handle('Parser', new JSONFunctionImpl('parseJson', new TypeImpl('Parser'),["[1,2,3]"])));
        $this->assertEquals(['a'=>1,2,3], $this->handler->handle('Parser', new JSONFunctionImpl('parseFunctionStringArray', new TypeImpl('Parser'),["['a'=>1,2,3]"])));
        $this->assertEquals([1,2,3], $this->handler->handle('Parser', new JSONFunctionImpl('parseFunctionStringArray', new TypeImpl('Parser'),["[1,2,3]"])));
        $this->assertEquals([1,2,'a'=>3], $this->handler->handle('Parser', new JSONFunctionImpl('parseFunctionStringArray', new TypeImpl('Parser'),["[1,2,'a'=>3]"])));
    }


}