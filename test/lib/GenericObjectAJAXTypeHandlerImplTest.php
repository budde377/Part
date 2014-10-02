<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/31/14
 * Time: 7:33 PM
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\ajax\AJAXTypeHandler;
use ChristianBudde\cbweb\controller\json\Element;
use ChristianBudde\cbweb\controller\ajax\GenericObjectAJAXTypeHandlerImpl;
use ChristianBudde\cbweb\controller\function_string\FunctionStringParserImpl;
use ChristianBudde\cbweb\controller\json\ObjectImpl;
use ChristianBudde\cbweb\controller\json\JSONFunction;
use ChristianBudde\cbweb\controller\json\Response;
use ChristianBudde\cbweb\controller\json\ResponseImpl;
use ChristianBudde\cbweb\model\page\Page;
use ChristianBudde\cbweb\test\stub\NullAJAXServerImpl;
use ChristianBudde\cbweb\test\stub\StubAJAXTypeHandlerImpl;

class GenericObjectAJAXTypeHandlerImplTest extends \PHPUnit_Framework_TestCase
{

    /** @var  Element */
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

        $this->object = new ObjectImpl('someObject');
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
        $this->assertEquals('ChristianBudde\cbweb\controller\json\Element', $list[1]);
        $this->assertEquals('ChristianBudde\cbweb\controller\json\Object', $list[2]);
        $this->assertEquals('Element', $list[3]);
        $this->assertEquals('Object', $list[4]);
    }

    public function testWhitelistTypeOfNonExistingTypeDoesNothing()
    {
        $this->handler->whitelistType('Page');
        $list = $this->handler->listTypes();
        $this->assertEquals(5, count($list));


    }


    public function testWhitelistTypeOfExistingTypeDoesWhitelist()
    {
        $this->handler->whitelistType('Element');
        $list = $this->handler->listTypes();
        $this->assertEquals(2, count($list));
        $this->assertEquals('Element', $list[1]);
        $this->assertEquals('ChristianBudde\cbweb\controller\json\Element', $list[0]);
    }


    public function testWhitelistTypeOfExistingTypeDoesWhitelistMultiple()
    {
        $this->handler->whitelistType('Element', 'Object');
        $list = $this->handler->listTypes();
        $this->assertEquals(4, count($list));
        $this->assertEquals('ChristianBudde\cbweb\controller\json\Element', $list[0]);
        $this->assertEquals('Element', $list[1]);
        $this->assertEquals('ChristianBudde\cbweb\controller\json\Object', $list[2]);
        $this->assertEquals('Object', $list[3]);
    }

    public function testWhitelistTypeOfExistingTypeDoesWhitelistMultipleFromConstructor()
    {

        $handler = new GenericObjectAJAXTypeHandlerImpl($this->object, "Element", "Object");

        $list = $handler->listTypes();
        $this->assertEquals(4, count($list));
        $this->assertEquals(['ChristianBudde\cbweb\controller\json\Element', 'Element', 'ChristianBudde\cbweb\controller\json\Object', 'Object'], $list);
    }


    public function testHasTypeOnWhitelistIsRight()
    {
        $this->handler->whitelistType('Element');
        $this->assertFalse($this->handler->hasType('Object'));
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
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $list = $this->handler->listFunctions('Object');
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

        $list = $this->handler->listFunctions('Element');
        $this->assertEquals(3, count($list));
        $this->assertEquals('getAsJSONString', $list[0]);
        $this->assertEquals('getAsArray', $list[1]);
        $this->assertEquals('jsonSerialize', $list[2]);


        $list = $this->handler->listFunctions('Object');
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

        $this->handler->whitelistFunction('Element', 'getAsJSONString');
        $this->setUpHandler($this->handler);
        $this->handler->whitelistFunction('Element', 'jsonSerialize');
        $list = $this->handler->listFunctions('Element');
        $this->assertEquals(['getAsJSONString', 'jsonSerialize'], $list);

    }

    public function testWhitelistFunctionDoesWhitelistWithMultiple()
    {

        $this->handler->whitelistFunction('Element', 'getAsJSONString', 'jsonSerialize');
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $list = $this->handler->listFunctions('Element');
        $this->assertEquals(['getAsJSONString', 'jsonSerialize'], $list);
    }

    public function testWhitelistFunctionWorksWhenAddingFunctionLater()
    {

        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->whitelistFunction('Element', 'custom');
        $this->handler->addFunction('Element', 'custom', function () {
        });
        $list = $this->handler->listFunctions('Element');
        $this->assertEquals(['custom'], $list);
    }

    public function testWhitelistFunctionDoesNotWhitelistNonExistingMethod()
    {

        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->whitelistFunction('Element', 'nonExistingFunction');
        $list = $this->handler->listFunctions('Element');
        $this->assertEquals(3, count($list));

    }

    public function testWhitelistFunctionDoesNotWhitelistNonExistingMethodInDifferentOrder()
    {


        $this->handler->whitelistFunction('Element', 'nonExistingFunction');
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $list = $this->handler->listFunctions('Element');
        $this->assertEquals(3, count($list));

    }

    public function testHasTypeReturnsTrueOnHasType()
    {
        $this->assertTrue($this->handler->hasType('Object'));
    }

    public function testHasTypeReturnsFalseOnDoesNotHaveType()
    {
        $this->assertFalse($this->handler->hasType('Page'));
    }


    public function testHasFunctionIsRight()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->assertTrue($this->handler->hasFunction('Element', 'getAsJSONString'));
        $this->handler->whitelistFunction('Element', 'jsonSerialize');
        $this->assertFalse($this->handler->hasFunction('Element', 'getAsJSONString'));
        $this->assertTrue($this->handler->hasFunction('Element', 'jsonSerialize'));
        $this->handler->whitelistFunction('Element', 'nonExistingFunction');
        $this->assertFalse($this->handler->hasFunction('Element', 'getAsJSONString'));
        $this->assertTrue($this->handler->hasFunction('Element', 'jsonSerialize'));

    }


    public function testCanHandleIsTrueWithRightFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        /** @var \ChristianBudde\cbweb\controller\json\JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('Element', $f));

    }

    public function testCanHandleIsFalseWithNonWhiteListedFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->whitelistFunction('Element', 'getAsArray');
        /** @var \ChristianBudde\cbweb\controller\json\JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString('test',123)");
        $this->assertFalse($this->handler->canHandle('Element', $f));

    }


    public function testCanHandleIsFalseWithNonWhiteListedCustomFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->whitelistFunction('Element', 'getAsArray');
        $this->handler->addFunction('Element', 'custom', function(){return "success";});
        /** @var \ChristianBudde\cbweb\controller\json\JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.custom()");
        $this->assertFalse($this->handler->canHandle('Element', $f));

    }


    public function testCanHandleIsFalseWithWrongFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.nonExistingFunction('test',123)");
        $this->assertFalse($this->handler->canHandle('Element', $f));

    }

    public function testHandleCallsFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');

        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString()");
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $r = $this->handler->handle('Element', $f);
        $this->assertEquals($this->object->getAsJSONString(), $r);

    }

    public function testHandleCallsFunctionAndInstance()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $o = new ObjectImpl('someNewObject');
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString()");
        $this->assertTrue($this->handler->canHandle('Element', $f, $o));
        /** @var Response $r */
        $r = $this->handler->handle('Element', $f, $o);
        $this->assertEquals($o->getAsJSONString(), $r);
    }

    public function testAuthFunctionIsPassedRightArguments()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');

        $args = [];
        $this->handler->addAuthFunction(function () use (&$args) {
            $args = func_get_args();
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $this->handler->handle('Element', $f);
        $this->assertEquals(['ChristianBudde\cbweb\controller\json\Element', $this->object, 'getAsJSONString', ['test', 123]], $args);

        $o = new ObjectImpl('someNewObject');
        $this->handler->handle('Element', $f, $o);
        $this->assertEquals(['ChristianBudde\cbweb\controller\json\Element', $o, 'getAsJSONString', ['test', 123]], $args);
    }

    public function testFunctionAuthFunctionIsPassedRightArguments()
    {

        $this->setUpHandler($this->handler);
        $args = [];
        $this->handler->addFunctionAuthFunction('Element', 'getAsJSONString', function () use (&$args) {
            $args = func_get_args();
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $this->handler->handle('Element', $f);
        $this->assertEquals(['ChristianBudde\cbweb\controller\json\Element', $this->object, 'getAsJSONString', ['test', 123]], $args);

        $o = new ObjectImpl('someNewObject');
        $this->handler->handle('Element', $f, $o);
        $this->assertEquals(['ChristianBudde\cbweb\controller\json\Element', $o, 'getAsJSONString', ['test', 123]], $args);
    }

    public function testTypeAuthFunctionIsPassedRightArguments()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');

        $args = [];
        $this->handler->addTypeAuthFunction('Element', function () use (&$args) {
            $args = func_get_args();
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $this->handler->handle('Element', $f);
        $this->assertEquals(['ChristianBudde\cbweb\controller\json\Element', $this->object, 'getAsJSONString', ['test', 123]], $args);

        $o = new ObjectImpl('someNewObject');
        $this->handler->handle('Element', $f, $o);
        $this->assertEquals(['ChristianBudde\cbweb\controller\json\Element', $o, 'getAsJSONString', ['test', 123]], $args);
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
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->addAuthFunction($this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $r = $this->handler->handle('Element', $f);
        $this->assertInstanceOf('ChristianBudde\cbweb\controller\json\Response', $r);
        $this->assertEquals(Response::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(Response::ERROR_CODE_UNAUTHORIZED, $r->getErrorCode());
    }

    public function testAddFunctionAuthFunctionChangesHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->addFunctionAuthFunction('Element', 'getAsJSONString', $this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $r = $this->handler->handle('Element', $f);
        $this->assertInstanceOf('ChristianBudde\cbweb\controller\json\Response', $r);
        $this->assertEquals(Response::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(Response::ERROR_CODE_UNAUTHORIZED, $r->getErrorCode());
    }

    public function testTypeFunctionAuthFunctionChangesHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->addTypeAuthFunction('Element', $this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $r = $this->handler->handle('Element', $f);
        $this->assertInstanceOf('ChristianBudde\cbweb\controller\json\Response', $r);
        $this->assertEquals(Response::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(Response::ERROR_CODE_UNAUTHORIZED, $r->getErrorCode());
    }

    public function testTypeFunctionAuthFunctionOnOtherTypeDoesNothingToHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->addFunctionAuthFunction('Object', 'getAsJSONString', $this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var \ChristianBudde\cbweb\controller\json\Response $r */
        $r = $this->handler->handle('Element', $f);
        $this->assertEquals($this->object->getAsJSONString(), $r);
    }


    public function testAuthTypeAuthFunctionOnOtherTypeDoesNothingToHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->addTypeAuthFunction('Object', $this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString('test',123)");
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var \ChristianBudde\cbweb\controller\json\Response $r */
        $r = $this->handler->handle('Element', $f);
        $this->assertEquals($this->object->getAsJSONString(), $r);
    }


    public function testAddFunctionAddsFunction()
    {

        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $args = [];
        $this->handler->addFunction('Element', 'getAsJSONString', function () use (&$args) {
            $args = func_get_args();
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.getAsJSONString(1,2,3)");

        $this->handler->handle('Element', $f);
        $this->assertEquals([$this->object, 1, 2, 3], $args);

        $o = new ObjectImpl('SomeNewString');
        $this->handler->handle('Element', $f, $o);
        $this->assertEquals([$o, 1, 2, 3], $args);

    }


    public function testPreCallFunctionIsCalledBeforeFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $a = [];
        $i = 1;
        $f = function ($type, $instance, $functionName, &$arguments) use (&$a, &$i) {
            $arguments[] = $i;
            $a[] = func_get_args();
            $i++;
        };
        $this->handler->addPreCallFunction($f);
        $this->handler->addTypePreCallFunction('Element', $f);
        $this->handler->addFunctionPreCallFunction('Element', 'custom', $f);

        $args = [];
        $this->handler->addFunction('Element', 'custom', function () use (&$args) {
            $args = func_get_args();
        });
        /** @var \ChristianBudde\cbweb\controller\json\JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.custom()");

        $this->handler->handle('Element', $f);
        $this->assertEquals([
            ['ChristianBudde\cbweb\controller\json\Element', $this->object, 'custom', [1]],
            ['ChristianBudde\cbweb\controller\json\Element', $this->object, 'custom', [1, 2]],
            ['ChristianBudde\cbweb\controller\json\Element', $this->object, 'custom', [1, 2, 3]]

        ], $a);
        $this->assertEquals([$this->object, 1, 2, 3], $args);


    }

    public function testPostCallFunctionIsCalledAfterFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $a = [];
        $i = 2;
        $f = function ($type, $instance, $functionName, &$result) use (&$a, &$i) {
            $result[] = $i;
            $a[] = func_get_args();
            $i++;
        };
        $this->handler->addPostCallFunction($f);
        $this->handler->addTypePostCallFunction('Element', $f);
        $this->handler->addFunctionPostCallFunction('Element', 'custom', $f);

        $args = [];
        $this->handler->addFunction('Element', 'custom', function () use (&$args) {
            return [1];
        });
        /** @var \ChristianBudde\cbweb\controller\json\JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.custom()");

        $r = $this->handler->handle('Element', $f);
        $this->assertEquals([
            ['ChristianBudde\cbweb\controller\json\Element', $this->object, 'custom', [1, 2]],
            ['ChristianBudde\cbweb\controller\json\Element', $this->object, 'custom', [1, 2, 3]],
            ['ChristianBudde\cbweb\controller\json\Element', $this->object, 'custom', [1, 2, 3, 4]]

        ], $a);
        $this->assertEquals([1, 2, 3, 4], $r);


    }


    public function testAddedFunctionIsInFunctionList()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->addFunction('Element', 'custom', function () {
        });
        $list = $this->handler->listFunctions('Element');
        $this->assertContains('custom', $list);
        $this->assertTrue($this->handler->hasFunction('Element', 'custom'));
    }


    public function testNonTypeStringToConstructorAddsNoFunctions()
    {

        $handler = new GenericObjectAJAXTypeHandlerImpl("NotARealType");
        $this->assertTrue($handler->hasType('NotARealType'));
        $this->assertEquals(0, count($handler->listFunctions('NotARealType')));

    }

    public function testStringToConstructorDoesNotAddDefaultInstance()
    {
        $handler = new GenericObjectAJAXTypeHandlerImpl("ChristianBudde\\cbweb\\model\\user\\User");
        $this->assertTrue($handler->hasType("ChristianBudde\\cbweb\\model\\user\\User"));
        $this->assertTrue($handler->hasType("User"));
        $handler->setUp(new NullAJAXServerImpl(), 'User');
        /** @var JSONFunction $f */

        $f = $this->parser->parseFunctionString('User.getName()');

        $r = $handler->handle('User', $f);

        $this->assertEquals(new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_NO_SUCH_FUNCTION), $r);

    }

    public function testStringToConstructorCanCallCustomFunctions()
    {
        $handler = new GenericObjectAJAXTypeHandlerImpl("ChristianBudde\\cbweb\\model\\user\\User");
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
        $this->handler->addGetInstanceFunction("Element");
        $this->handler->setUp(new NullAJAXServerImpl(), "Element");
        $list = $this->handler->listFunctions("Element");
        $this->assertContains("getInstance", $list);
        /** @var JSONFunction $function */
        $function = $this->parser->parseFunctionString("Element.getInstance()");
        $r = $this->handler->handle("Element", $function, $this);
        $this->assertTrue($this === $r);
    }


    public function testStringOfActualTypeDoesAddTypesAndFunctions()
    {
        $handler = new GenericObjectAJAXTypeHandlerImpl("ChristianBudde\\cbweb\\controller\\json\\Object");
        $this->assertTrue($handler->hasType("Element"));

        $handler = new GenericObjectAJAXTypeHandlerImpl("ChristianBudde\\cbweb\\controller\\json\\Object");
        $this->assertTrue($handler->hasType("Element"));


    }

    public function testSetUpBogusElementIsOk()
    {
        $handler = new GenericObjectAJAXTypeHandlerImpl("NonExistingObject");
        $this->setUpHandler($handler);

    }


    public function testAddFunctionAuthIsOnlyAddedToOne()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction("Element", "custom1", $f = function () {
            return true;
        });
        $this->handler->addFunction("Element", "custom2", $f);

        $this->handler->addFunctionAuthFunction('Element', 'custom1', function () {
            return false;
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element.custom2()");
        $this->assertTrue($this->handler->handle('Element', $f));


    }


    public function testCanNotHandleWithWrongNumberOfArguments()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function (Element $element, array $a) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element . custom()");
        $this->assertFalse($this->handler->canHandle('Element', $f));
    }


    public function testCanHandleWithSomeArgumentsBeingOptional()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function (Element $element, array $a = []) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element . custom()");
        $this->assertTrue($this->handler->canHandle('Element', $f));
    }

    public function testCanHandleWithSomeMiddleArgumentsBeingOptional()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function (Element $element, array $a = [], $v) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element . custom([])");
        $this->assertFalse($this->handler->canHandle('Element', $f));
    }

    public function testCanNotHandleWithWrongArguments()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function (Element $element, array $a) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element . custom('string')");
        $this->assertFalse($this->handler->canHandle('Element', $f));
    }

    public function testCanNotHandleWithWrongArgumentsType()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function (Element $element, Page $a) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseFunctionString("Element . custom('string')");
        $this->assertFalse($this->handler->canHandle('Element', $f));
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

    //TODO test for null value to Typed parameter. This is not allowed in PHP.


    private function setUpHandler(AJAXTypeHandler $handler)
    {
        foreach ($handler->listTypes() as $type) {
            $handler->setUp($this->nullAJAXServer, $type);
        }
    }


}