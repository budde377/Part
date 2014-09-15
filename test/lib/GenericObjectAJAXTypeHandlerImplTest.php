<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/31/14
 * Time: 7:33 PM
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\ajax\AJAXTypeHandler;
use ChristianBudde\cbweb\controller\json\JSONElement;
use ChristianBudde\cbweb\controller\ajax\GenericObjectAJAXTypeHandlerImpl;
use ChristianBudde\cbweb\controller\function_string\FunctionStringParserImpl;
use ChristianBudde\cbweb\controller\json\JSONObjectImpl;
use ChristianBudde\cbweb\controller\json\JSONFunction;
use ChristianBudde\cbweb\controller\json\JSONResponse;
use ChristianBudde\cbweb\controller\json\JSONResponseImpl;
use ChristianBudde\cbweb\model\page\Page;
use ChristianBudde\cbweb\test\stub\NullAJAXServerImpl;
use ChristianBudde\cbweb\test\stub\StubAJAXTypeHandlerImpl;

class GenericObjectAJAXTypeHandlerImplTest extends \PHPUnit_Framework_TestCase
{

    /** @var  JSONElement */
    private $object;
    /** @var  GenericObjectAJAXTypeHandlerImpl */
    private $handler;

    private $nullAJAXServer;
    /** @var  FunctionStringParserImpl */
    private $parser;
    private $falseFunction;
    private $trueFunction;

    protected function setUp()
    {

        $this->object = new JSONObjectImpl('someObject');
        $this->handler = new GenericObjectAJAXTypeHandlerImpl($this->object);
        $this->nullAJAXServer = new stub\NullAJAXServerImpl();
        $this->parser = new FunctionStringParserImpl();
        $this->falseFunction = function () {
            return false;
        };
        $this->trueFunction = function () {
            return true;
        };
    }


    public function testGetObjectReturnsRightObject()
    {
        $this->assertTrue($this->object === $this->handler->getObject());
    }

    public function testListTypesGetsFromObject()
    {
        $list = $this->handler->listTypes();
        $this->assertTrue(is_array($list));
        $this->assertEquals(5, count($list));
        $this->assertEquals('JsonSerializable', $list[0]);
        $this->assertEquals('ChristianBudde\cbweb\JSONElement', $list[1]);
        $this->assertEquals('ChristianBudde\cbweb\JSONObject', $list[2]);
        $this->assertEquals('JSONElement', $list[3]);
        $this->assertEquals('JSONObject', $list[4]);
    }

    public function testWhitelistTypeOfNonExistingTypeDoesNothing()
    {
        $this->handler->whitelistType('Page');
        $list = $this->handler->listTypes();
        $this->assertEquals(5, count($list));


    }


    public function testWhitelistTypeOfExistingTypeDoesWhitelist()
    {
        $this->handler->whitelistType('JSONElement');
        $list = $this->handler->listTypes();
        $this->assertEquals(2, count($list));
        $this->assertEquals('JSONElement', $list[1]);
        $this->assertEquals('ChristianBudde\cbweb\JSONElement', $list[0]);
    }


    public function testWhitelistTypeOfExistingTypeDoesWhitelistMultiple()
    {
        $this->handler->whitelistType('JSONElement', 'JSONObject');
        $list = $this->handler->listTypes();
        $this->assertEquals(4, count($list));
        $this->assertEquals('ChristianBudde\cbweb\JSONElement', $list[0]);
        $this->assertEquals('JSONElement', $list[1]);
        $this->assertEquals('ChristianBudde\cbweb\JSONObject', $list[2]);
        $this->assertEquals('JSONObject', $list[3]);
    }

    public function testWhitelistTypeOfExistingTypeDoesWhitelistMultipleFromConstructor()
    {

        $handler = new GenericObjectAJAXTypeHandlerImpl($this->object, "JSONElement", "JSONObject");

        $list = $handler->listTypes();
        $this->assertEquals(4, count($list));
        $this->assertEquals(['ChristianBudde\cbweb\JSONElement', 'JSONElement', 'ChristianBudde\cbweb\JSONObject', 'JSONObject'], $list);
    }


    public function testHasTypeOnWhitelistIsRight()
    {
        $this->handler->whitelistType('JSONElement');
        $this->assertFalse($this->handler->hasType('JSONObject'));
        $this->assertFalse($this->handler->hasType('JsonSerializable'));
    }

    public function testListFunctionsOfNonExistingTypeReturnsEmptyArray()
    {
        $list = $this->handler->listFunctions('Page');
        $this->assertTrue(is_array($list));
        $this->assertEquals(0, count($list));


    }

    public function testListFunctionOfNonSetupDoesNothing()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $list = $this->handler->listFunctions('JSONObject');
        $this->assertTrue(is_array($list));
        $this->assertEquals(0, count($list));


    }

    public function testListFunctionsListsFunctions()
    {

        $this->setUpHandler($this->handler);

        $list = $this->handler->listFunctions('JsonSerializable');
        $this->assertTrue(is_array($list));
        $this->assertEquals(1, count($list));
        $this->assertEquals('jsonSerialize', $list[0]);

        $list = $this->handler->listFunctions('JSONElement');
        $this->assertEquals(3, count($list));
        $this->assertEquals('getAsJSONString', $list[0]);
        $this->assertEquals('getAsArray', $list[1]);
        $this->assertEquals('jsonSerialize', $list[2]);


        $list = $this->handler->listFunctions('JSONObject');
        $this->assertEquals(6, count($list));
        $this->assertEquals('getName', $list[0]);
        $this->assertEquals('setVariable', $list[1]);
        $this->assertEquals('getVariable', $list[2]);
        $this->assertEquals('getAsJSONString', $list[3]);
        $this->assertEquals('getAsArray', $list[4]);
        $this->assertEquals('jsonSerialize', $list[5]);
    }

    public function testWhitelistFunctionDoesWhitelist()
    {

        $this->handler->whitelistFunction('JSONElement', 'getAsJSONString');
        $this->setUpHandler($this->handler);
        $this->handler->whitelistFunction('JSONElement', 'jsonSerialize');
        $list = $this->handler->listFunctions('JSONElement');
        $this->assertEquals(['getAsJSONString', 'jsonSerialize'], $list);

    }

    public function testWhitelistFunctionDoesWhitelistWithMultiple()
    {

        $this->handler->whitelistFunction('JSONElement', 'getAsJSONString', 'jsonSerialize');
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $list = $this->handler->listFunctions('JSONElement');
        $this->assertEquals(['getAsJSONString', 'jsonSerialize'], $list);
    }

    public function testWhitelistFunctionWorksWhenAddingFunctionLater()
    {

        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $this->handler->whitelistFunction('JSONElement', 'custom');
        $this->handler->addFunction('JSONElement', 'custom', function () {
        });
        $list = $this->handler->listFunctions('JSONElement');
        $this->assertEquals(['custom'], $list);
    }

    public function testWhitelistFunctionDoesNotWhitelistNonExistingMethod()
    {


        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $this->handler->whitelistFunction('JSONElement', 'nonExistingFunction');
        $list = $this->handler->listFunctions('JSONElement');
        $this->assertEquals(3, count($list));

    }

    public function testWhitelistFunctionDoesNotWhitelistNonExistingMethodInDifferentOrder()
    {


        $this->handler->whitelistFunction('JSONElement', 'nonExistingFunction');
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $list = $this->handler->listFunctions('JSONElement');
        $this->assertEquals(3, count($list));

    }

    public function testHasTypeReturnsTrueOnHasType()
    {
        $this->assertTrue($this->handler->hasType('JSONObject'));
    }

    public function testHasTypeReturnsFalseOnDoesNotHaveType()
    {
        $this->assertFalse($this->handler->hasType('Page'));
    }


    public function testHasFunctionIsRight()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $this->assertTrue($this->handler->hasFunction('JSONElement', 'getAsJSONString'));
        $this->handler->whitelistFunction('JSONElement', 'jsonSerialize');
        $this->assertFalse($this->handler->hasFunction('JSONElement', 'getAsJSONString'));
        $this->assertTrue($this->handler->hasFunction('JSONElement', 'jsonSerialize'));
        $this->handler->whitelistFunction('JSONElement', 'nonExistingFunction');
        $this->assertFalse($this->handler->hasFunction('JSONElement', 'getAsJSONString'));
        $this->assertTrue($this->handler->hasFunction('JSONElement', 'jsonSerialize'));

    }


    public function testCanHandleIsTrueWithRightFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        /** @var \ChristianBudde\cbweb\controller\json\JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('JSONElement', $f));

    }


    public function testCanHandleIsFalseWithWrongFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.nonExistingFunction('test',123)");
        $this->assertFalse($this->handler->canHandle('JSONElement', $f));

    }

    public function testHandleCallsFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');

        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.getAsJSONString()");
        $this->assertTrue($this->handler->canHandle('JSONElement', $f));
        /** @var JSONResponse $r */
        $r = $this->handler->handle('JSONElement', $f);
        $this->assertEquals($this->object->getAsJSONString(), $r);

    }

    public function testHandleCallsFunctionAndInstance()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $o = new JSONObjectImpl('someNewObject');
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.getAsJSONString()");
        $this->assertTrue($this->handler->canHandle('JSONElement', $f, $o));
        /** @var JSONResponse $r */
        $r = $this->handler->handle('JSONElement', $f, $o);
        $this->assertEquals($o->getAsJSONString(), $r);
    }

    public function testAuthFunctionIsPassedRightArguments()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');

        $args = [];
        $this->handler->addAuthFunction(function () use (&$args) {
            $args = func_get_args();
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('JSONElement', $f));
        /** @var JSONResponse $r */
        $this->handler->handle('JSONElement', $f);
        $this->assertEquals(['ChristianBudde\cbweb\JSONElement', $this->object, 'getAsJSONString', ['test', 123]], $args);

        $o = new JSONObjectImpl('someNewObject');
        $this->handler->handle('JSONElement', $f, $o);
        $this->assertEquals(['ChristianBudde\cbweb\JSONElement', $o, 'getAsJSONString', ['test', 123]], $args);
    }

    public function testFunctionAuthFunctionIsPassedRightArguments()
    {

        $this->setUpHandler($this->handler);
        $args = [];
        $this->handler->addFunctionAuthFunction('JSONElement', 'getAsJSONString', function () use (&$args) {
            $args = func_get_args();
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('JSONElement', $f));
        /** @var JSONResponse $r */
        $this->handler->handle('JSONElement', $f);
        $this->assertEquals(['ChristianBudde\cbweb\JSONElement', $this->object, 'getAsJSONString', ['test', 123]], $args);

        $o = new JSONObjectImpl('someNewObject');
        $this->handler->handle('JSONElement', $f, $o);
        $this->assertEquals(['ChristianBudde\cbweb\JSONElement', $o, 'getAsJSONString', ['test', 123]], $args);
    }

    public function testTypeAuthFunctionIsPassedRightArguments()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');

        $args = [];
        $this->handler->addTypeAuthFunction('JSONElement', function () use (&$args) {
            $args = func_get_args();
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('JSONElement', $f));
        /** @var JSONResponse $r */
        $this->handler->handle('JSONElement', $f);
        $this->assertEquals(['ChristianBudde\cbweb\JSONElement', $this->object, 'getAsJSONString', ['test', 123]], $args);

        $o = new JSONObjectImpl('someNewObject');
        $this->handler->handle('JSONElement', $f, $o);
        $this->assertEquals(['ChristianBudde\cbweb\JSONElement', $o, 'getAsJSONString', ['test', 123]], $args);
    }

    public function testHandleCallsWithRightArguments()
    {
        $handler = new GenericObjectAJAXTypeHandlerImpl($h = new StubAJAXTypeHandlerImpl());
        $handler->setUp(new NullAJAXServerImpl(), 'AJAXTypeHandler');
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString('AJAXTypeHandler.hasType("asd",123)');
        $handler->handle('AJAXTypeHandler', $f);

        $this->assertEquals(['method' => 'hasType', 'arguments' => ['asd', 123]], $h->calledMethods[1]);

    }


    public function testAddAuthFunctionChangesHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $this->handler->addAuthFunction($this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('JSONElement', $f));
        /** @var JSONResponse $r */
        $r = $this->handler->handle('JSONElement', $f);
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertEquals(JSONResponse::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(JSONResponse::ERROR_CODE_UNAUTHORIZED, $r->getErrorCode());
    }

    public function testAddFunctionAuthFunctionChangesHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $this->handler->addFunctionAuthFunction('JSONElement', 'getAsJSONString', $this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('JSONElement', $f));
        /** @var JSONResponse $r */
        $r = $this->handler->handle('JSONElement', $f);
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertEquals(JSONResponse::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(JSONResponse::ERROR_CODE_UNAUTHORIZED, $r->getErrorCode());
    }

    public function testTypeFunctionAuthFunctionChangesHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $this->handler->addTypeAuthFunction('JSONElement', $this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('JSONElement', $f));
        /** @var JSONResponse $r */
        $r = $this->handler->handle('JSONElement', $f);
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertEquals(JSONResponse::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(JSONResponse::ERROR_CODE_UNAUTHORIZED, $r->getErrorCode());
    }

    public function testTypeFunctionAuthFunctionOnOtherTypeDoesNothingToHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $this->handler->addFunctionAuthFunction('JSONObject', 'getAsJSONString', $this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('JSONElement', $f));
        /** @var \ChristianBudde\cbweb\controller\json\JSONResponse $r */
        $r = $this->handler->handle('JSONElement', $f);
        $this->assertEquals($this->object->getAsJSONString(), $r);
    }


    public function testAuthTypeAuthFunctionOnOtherTypeDoesNothingToHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $this->handler->addTypeAuthFunction('JSONObject', $this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('JSONElement', $f));
        /** @var \ChristianBudde\cbweb\controller\json\JSONResponse $r */
        $r = $this->handler->handle('JSONElement', $f);
        $this->assertEquals($this->object->getAsJSONString(), $r);
    }


    public function testAddFunctionAddsFunction()
    {

        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $args = [];
        $this->handler->addFunction('JSONElement', 'getAsJSONString', function () use (&$args) {
            $args = func_get_args();
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.getAsJSONString(1,2,3)");

        $this->handler->handle('JSONElement', $f);
        $this->assertEquals([$this->object, 1, 2, 3], $args);

        $o = new JSONObjectImpl('SomeNewString');
        $this->handler->handle('JSONElement', $f, $o);
        $this->assertEquals([$o, 1, 2, 3], $args);

    }


    public function testPreCallFunctionIsCalledBeforeFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $a = [];
        $i = 1;
        $f = function ($type, $instance, $functionName, &$arguments) use (&$a, &$i) {
            $arguments[] = $i;
            $a[] = func_get_args();
            $i++;
        };
        $this->handler->addPreCallFunction($f);
        $this->handler->addTypePreCallFunction('JSONElement', $f);
        $this->handler->addFunctionPreCallFunction('JSONElement', 'custom', $f);

        $args = [];
        $this->handler->addFunction('JSONElement', 'custom', function () use (&$args) {
            $args = func_get_args();
        });
        /** @var \ChristianBudde\cbweb\controller\json\JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.custom()");

        $this->handler->handle('JSONElement', $f);
        $this->assertEquals([
            ['ChristianBudde\cbweb\JSONElement', $this->object, 'custom', [1]],
            ['ChristianBudde\cbweb\JSONElement', $this->object, 'custom', [1, 2]],
            ['ChristianBudde\cbweb\JSONElement', $this->object, 'custom', [1, 2, 3]]

        ], $a);
        $this->assertEquals([$this->object, 1, 2, 3], $args);


    }

    public function testPostCallFunctionIsCalledAfterFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $a = [];
        $i = 2;
        $f = function ($type, $instance, $functionName, &$result) use (&$a, &$i) {
            $result[] = $i;
            $a[] = func_get_args();
            $i++;
        };
        $this->handler->addPostCallFunction($f);
        $this->handler->addTypePostCallFunction('JSONElement', $f);
        $this->handler->addFunctionPostCallFunction('JSONElement', 'custom', $f);

        $args = [];
        $this->handler->addFunction('JSONElement', 'custom', function () use (&$args) {
            return [1];
        });
        /** @var \ChristianBudde\cbweb\controller\json\JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.custom()");

        $r = $this->handler->handle('JSONElement', $f);
        $this->assertEquals([
            ['ChristianBudde\cbweb\JSONElement', $this->object, 'custom', [1, 2]],
            ['ChristianBudde\cbweb\JSONElement', $this->object, 'custom', [1, 2, 3]],
            ['ChristianBudde\cbweb\JSONElement', $this->object, 'custom', [1, 2, 3, 4]]

        ], $a);
        $this->assertEquals([1, 2, 3, 4], $r);


    }


    public function testAddedFunctionIsInFunctionList()
    {
        $this->handler->setUp($this->nullAJAXServer, 'JSONElement');
        $this->handler->addFunction('JSONElement', 'custom', function () {
        });
        $list = $this->handler->listFunctions('JSONElement');
        $this->assertContains('custom', $list);
        $this->assertTrue($this->handler->hasFunction('JSONElement', 'custom'));
    }


    public function testNonTypeStringToConstructorAddsNoFunctions()
    {

        $handler = new GenericObjectAJAXTypeHandlerImpl("NotARealType");
        $this->assertTrue($handler->hasType('NotARealType'));
        $this->assertEquals(0, count($handler->listFunctions('NotARealType')));

    }

    public function testStringToConstructorDoesNotAddDefaultInstance()
    {
        $handler = new GenericObjectAJAXTypeHandlerImpl("ChristianBudde\\cbweb\\User");
        $this->assertTrue($handler->hasType("ChristianBudde\\cbweb\\User"));
        $this->assertTrue($handler->hasType("User"));
        $handler->setUp(new NullAJAXServerImpl(), 'User');
        /** @var JSONFunction $f */

        $f = $this->parser->parseFunctionString('User.getName()');

        $r = $handler->handle('User', $f);

        $this->assertEquals(new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_NO_SUCH_FUNCTION), $r);

    }

    public function testStringToConstructorCanCallCustomFunctions()
    {
        $handler = new GenericObjectAJAXTypeHandlerImpl("ChristianBudde\\cbweb\\User");
        $this->assertTrue($handler->hasType("User"));
        $handler->setUp(new NullAJAXServerImpl(), 'User');
        $args = [];
        /** @var JSONFunction $f */
        $handler->addFunction('User', 'custom', function () use (&$args) {
            $args = func_get_args();
        });
        $f = $this->parser->parseFunctionString('User.custom(1,2,3)');
        $r = $handler->handle('User', $f);
        $this->assertNull($r);
        $this->assertEquals([null, 1, 2, 3], $args);


    }

    public function testAddGetInstanceFunctionAddsFunction()
    {
        $this->handler->addGetInstanceFunction("JSONElement");
        $this->handler->setUp(new NullAJAXServerImpl(), "JSONElement");
        $list = $this->handler->listFunctions("JSONElement");
        $this->assertContains("getInstance", $list);
        /** @var JSONFunction $function */
        $function = $this->parser->parseFunctionString("JSONElement.getInstance()");
        $r = $this->handler->handle("JSONElement", $function, $this);
        $this->assertTrue($this === $r);
    }


    public function testStringOfActualTypeDoesAddTypesAndFunctions()
    {
        $handler = new GenericObjectAJAXTypeHandlerImpl("ChristianBudde\\cbweb\\JSONObject");
        $this->assertTrue($handler->hasType("JSONElement"));

        $handler = new GenericObjectAJAXTypeHandlerImpl("ChristianBudde\\cbweb\\JSONObject");
        $this->assertTrue($handler->hasType("JSONElement"));


    }

    public function testSetUpBogusElementIsOk()
    {
        $handler = new GenericObjectAJAXTypeHandlerImpl("NonExistingObject");
        $this->setUpHandler($handler);

    }


    public function testAddFunctionAuthIsOnlyAddedToOne()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction("JSONElement", "custom1", $f = function () {
            return true;
        });
        $this->handler->addFunction("JSONElement", "custom2", $f);

        $this->handler->addFunctionAuthFunction('JSONElement', 'custom1', function () {
            return false;
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement.custom2()");
        $this->assertTrue($this->handler->handle('JSONElement', $f));


    }


    public function testCanNotHandleWithWrongNumberOfArguments()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('JSONElement', 'custom', function (JSONElement $element, array $a) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement . custom()");
        $this->assertFalse($this->handler->canHandle('JSONElement', $f));
    }


    public function testCanHandleWithSomeArgumentsBeingOptional()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('JSONElement', 'custom', function (JSONElement $element, array $a = []) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement . custom()");
        $this->assertTrue($this->handler->canHandle('JSONElement', $f));
    }

    public function testCanHandleWithSomeMiddleArgumentsBeingOptional()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('JSONElement', 'custom', function (JSONElement $element, array $a = [], $v) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement . custom([])");
        $this->assertFalse($this->handler->canHandle('JSONElement', $f));
    }

    public function testCanNotHandleWithWrongArguments()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('JSONElement', 'custom', function (JSONElement $element, array $a) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement . custom('string')");
        $this->assertFalse($this->handler->canHandle('JSONElement', $f));
    }

    public function testCanNotHandleWithWrongArgumentsType()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('JSONElement', 'custom', function (JSONElement $element, Page $a) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONElement . custom('string')");
        $this->assertFalse($this->handler->canHandle('JSONElement', $f));
    }

    public function testCanCallFunctionOnObjectWithoutFunctions()
    {

        $handler = new GenericObjectAJAXTypeHandlerImpl("JSONProgram");
        $this->setUpHandler($handler);
        $handler->addFunction('JSONProgram', 'custom', function (array $a) {
        });
        /** @var \ChristianBudde\cbweb\controller\json\JSONFunction $f */
        $f = $this->parser->parseFunctionString("JSONProgram . custom([])");
        $this->assertTrue($handler->canHandle('JSONProgram', $f));
    }


    private function setUpHandler(AJAXTypeHandler $handler)
    {
        foreach ($handler->listTypes() as $type) {
            $handler->setUp($this->nullAJAXServer, $type);
        }
    }


}