<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/31/14
 * Time: 7:33 PM
 */
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\controller\ajax\type_handler\GenericObjectTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\function_string\Parser;
use ChristianBudde\Part\controller\function_string\ParserImpl;
use ChristianBudde\Part\controller\json\Element;
use ChristianBudde\Part\controller\json\JSONFunction;
use ChristianBudde\Part\controller\json\Object;
use ChristianBudde\Part\controller\json\ObjectImpl;
use ChristianBudde\Part\controller\json\Response;
use ChristianBudde\Part\controller\json\ResponseImpl;
use ChristianBudde\Part\model\page\Page;
use ChristianBudde\Part\test\stub\NullAJAXServerImpl;
use ChristianBudde\Part\test\stub\StubAJAXTypeHandlerImpl;

class GenericObjectAJAXTypeHandlerImplTest extends \PHPUnit_Framework_TestCase
{

    /** @var  Element */
    private $object;
    /** @var  GenericObjectTypeHandlerImpl */
    private $handler;

    private $nullAJAXServer;
    private $falseFunction;
    private $trueFunction;
    /** @var  Parser */
    private $parser;

    protected function setUp()
    {

        $this->object = new ObjectImpl('someObject');
        $this->handler = new GenericObjectTypeHandlerImpl($this->object);
        $this->nullAJAXServer = new stub\NullAJAXServerImpl();
        $this->falseFunction = function () {
            return false;
        };
        $this->trueFunction = function () {
            return true;
        };
        $this->parser = new ParserImpl();
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
        $this->assertEquals('ChristianBudde\Part\controller\json\Element', $list[1]);
        $this->assertEquals('ChristianBudde\Part\controller\json\Object', $list[2]);
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
        $this->assertEquals('ChristianBudde\Part\controller\json\Element', $list[0]);
    }


    public function testWhitelistTypeOfExistingTypeDoesWhitelistMultiple()
    {
        $this->handler->whitelistType('Element', 'Object');
        $list = $this->handler->listTypes();
        $this->assertEquals(4, count($list));
        $this->assertEquals('ChristianBudde\Part\controller\json\Element', $list[0]);
        $this->assertEquals('Element', $list[1]);
        $this->assertEquals('ChristianBudde\Part\controller\json\Object', $list[2]);
        $this->assertEquals('Object', $list[3]);
    }

    public function testWhitelistTypeOfExistingTypeDoesWhitelistMultipleFromConstructor()
    {

        $handler = new GenericObjectTypeHandlerImpl($this->object, "Element", "Object");

        $list = $handler->listTypes();
        $this->assertEquals(4, count($list));
        $this->assertEquals(['ChristianBudde\Part\controller\json\Element', 'Element', 'ChristianBudde\Part\controller\json\Object', 'Object'], $list);
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
        /** @var \ChristianBudde\Part\controller\json\JSONFunction $f */
        $f = $this->parser->parseString("Element.getAsJSONString('test',123)")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));

    }

    public function testCanHandleIsFalseWithNonWhiteListedFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->whitelistFunction('Element', 'getAsArray');
        /** @var \ChristianBudde\Part\controller\json\JSONFunction $f */
        $f = $this->parser->parseString("Element.getAsJSONString('test',123)")->toJSONProgram();
        $this->assertFalse($this->handler->canHandle('Element', $f));

    }


    public function testCanHandleIsFalseWithNonWhiteListedCustomFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->whitelistFunction('Element', 'getAsArray');
        $this->handler->addFunction('Element', 'custom', function(){return "success";});
        /** @var \ChristianBudde\Part\controller\json\JSONFunction $f */
        $f = $this->parser->parseString("Element.custom()")->toJSONProgram();
        $this->assertFalse($this->handler->canHandle('Element', $f));

    }


    public function testCanHandleIsFalseWithWrongFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element.nonExistingFunction('test',123)")->toJSONProgram();
        $this->assertFalse($this->handler->canHandle('Element', $f));

    }

    public function testHandleCallsFunction()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');

        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element.getAsJSONString()")->toJSONProgram();
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
        $f = $this->parser->parseString("Element.getAsJSONString()")->toJSONProgram();
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
        $f = $this->parser->parseString("Element.getAsJSONString('test',123)")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $this->handler->handle('Element', $f);
        $this->assertEquals(['ChristianBudde\Part\controller\json\Element', $this->object, 'getAsJSONString', ['test', 123]], $args);

        $o = new ObjectImpl('someNewObject');
        $this->handler->handle('Element', $f, $o);
        $this->assertEquals(['ChristianBudde\Part\controller\json\Element', $o, 'getAsJSONString', ['test', 123]], $args);
    }

    public function testFunctionAuthFunctionIsPassedRightArguments()
    {

        $this->setUpHandler($this->handler);
        $args = [];
        $this->handler->addFunctionAuthFunction('Element', 'getAsJSONString', function () use (&$args) {
            $args = func_get_args();
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element.getAsJSONString('test',123)")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $this->handler->handle('Element', $f);
        $this->assertEquals(['ChristianBudde\Part\controller\json\Element', $this->object, 'getAsJSONString', ['test', 123]], $args);

        $o = new ObjectImpl('someNewObject');
        $this->handler->handle('Element', $f, $o);
        $this->assertEquals(['ChristianBudde\Part\controller\json\Element', $o, 'getAsJSONString', ['test', 123]], $args);
    }

    public function testTypeAuthFunctionIsPassedRightArguments()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');

        $args = [];
        $this->handler->addTypeAuthFunction('Element', function () use (&$args) {
            $args = func_get_args();
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element.getAsJSONString('test',123)")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $this->handler->handle('Element', $f);
        $this->assertEquals(['ChristianBudde\Part\controller\json\Element', $this->object, 'getAsJSONString', ['test', 123]], $args);

        $o = new ObjectImpl('someNewObject');
        $this->handler->handle('Element', $f, $o);
        $this->assertEquals(['ChristianBudde\Part\controller\json\Element', $o, 'getAsJSONString', ['test', 123]], $args);
    }

    public function testHandleCallsWithRightArguments()
    {
        $handler = new GenericObjectTypeHandlerImpl($h = new StubAJAXTypeHandlerImpl());
        $handler->setUp(new NullAJAXServerImpl(), 'AJAXTypeHandler');
        /** @var JSONFunction $f */
        $f = $this->parser->parseString('AJAXTypeHandler.hasType("asd",123)')->toJSONProgram();
        $handler->handle('AJAXTypeHandler', $f);

        $this->assertEquals(['method' => 'hasType', 'arguments' => ['asd', 123]], $h->calledMethods[1]);

    }


    public function testAddAuthFunctionChangesHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->addAuthFunction($this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element.getAsJSONString('test',123)")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $r = $this->handler->handle('Element', $f);
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
        $this->assertEquals(Response::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(Response::ERROR_CODE_UNAUTHORIZED, $r->getErrorCode());
    }

    public function testAddFunctionAuthFunctionChangesHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->addFunctionAuthFunction('Element', 'getAsJSONString', $this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element.getAsJSONString('test',123)")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $r = $this->handler->handle('Element', $f);
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
        $this->assertEquals(Response::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(Response::ERROR_CODE_UNAUTHORIZED, $r->getErrorCode());
    }

    public function testTypeFunctionAuthFunctionChangesHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->addTypeAuthFunction('Element', $this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element.getAsJSONString('test',123)")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var Response $r */
        $r = $this->handler->handle('Element', $f);
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
        $this->assertEquals(Response::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(Response::ERROR_CODE_UNAUTHORIZED, $r->getErrorCode());
    }

    public function testTypeFunctionAuthFunctionOnOtherTypeDoesNothingToHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->addFunctionAuthFunction('Object', 'getAsJSONString', $this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element.getAsJSONString('test',123)")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var \ChristianBudde\Part\controller\json\Response $r */
        $r = $this->handler->handle('Element', $f);
        $this->assertEquals($this->object->getAsJSONString(), $r);
    }


    public function testAuthTypeAuthFunctionOnOtherTypeDoesNothingToHandle()
    {
        $this->handler->setUp($this->nullAJAXServer, 'Element');
        $this->handler->addTypeAuthFunction('Object', $this->falseFunction);
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element.getAsJSONString('test',123)")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));
        /** @var \ChristianBudde\Part\controller\json\Response $r */
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
        $f = $this->parser->parseString("Element.getAsJSONString(1,2,3)")->toJSONProgram();

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
        /** @var \ChristianBudde\Part\controller\json\JSONFunction $f */
        $f = $this->parser->parseString("Element.custom()")->toJSONProgram();

        $this->handler->handle('Element', $f);
        $this->assertEquals([
            ['ChristianBudde\Part\controller\json\Element', $this->object, 'custom', [1]],
            ['ChristianBudde\Part\controller\json\Element', $this->object, 'custom', [1, 2]],
            ['ChristianBudde\Part\controller\json\Element', $this->object, 'custom', [1, 2, 3]],
            ['ChristianBudde\Part\controller\json\Element', $this->object, 'custom', [4]],
            ['ChristianBudde\Part\controller\json\Element', $this->object, 'custom', [4, 5]],
            ['ChristianBudde\Part\controller\json\Element', $this->object, 'custom', [4, 5, 6]],

        ], $a);
        $this->assertEquals([$this->object, 4, 5, 6], $args);


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
        /** @var \ChristianBudde\Part\controller\json\JSONFunction $f */
        $f = $this->parser->parseString("Element.custom()")->toJSONProgram();

        $r = $this->handler->handle('Element', $f);
        $this->assertEquals([
            ['ChristianBudde\Part\controller\json\Element', $this->object, 'custom', [1, 2]],
            ['ChristianBudde\Part\controller\json\Element', $this->object, 'custom', [1, 2, 3]],
            ['ChristianBudde\Part\controller\json\Element', $this->object, 'custom', [1, 2, 3, 4]]

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

        $handler = new GenericObjectTypeHandlerImpl("NotARealType");
        $this->assertTrue($handler->hasType('NotARealType'));
        $this->assertEquals(0, count($handler->listFunctions('NotARealType')));

    }

    public function testStringToConstructorDoesNotAddDefaultInstance()
    {
        $handler = new GenericObjectTypeHandlerImpl("ChristianBudde\\Part\\model\\user\\User");
        $this->assertTrue($handler->hasType("ChristianBudde\\Part\\model\\user\\User"));
        $this->assertTrue($handler->hasType("User"));
        $handler->setUp(new NullAJAXServerImpl(), 'User');
        /** @var JSONFunction $f */

        $f = $this->parser->parseString('User.getName()')->toJSONProgram();

        $r = $handler->handle('User', $f);

        $this->assertEquals(new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_NO_SUCH_FUNCTION), $r);

    }

    public function testStringToConstructorCanCallCustomFunctions()
    {
        $handler = new GenericObjectTypeHandlerImpl("ChristianBudde\\Part\\model\\user\\User");
        $this->assertTrue($handler->hasType("User"));
        $handler->setUp(new NullAJAXServerImpl(), 'User');
        $args = [];
        /** @var JSONFunction $f */
        $handler->addFunction('User', 'custom', function () use (&$args) {
            $args = func_get_args();
        });
        $f = $this->parser->parseString('User.custom(1,2,3)')->toJSONProgram();
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
        $function = $this->parser->parseString("Element.getInstance()")->toJSONProgram();
        $r = $this->handler->handle("Element", $function, $this);
        $this->assertTrue($this === $r);
    }

    public function testAddCustomFunctionWithWhitelistIsOk()
    {
        $this->handler->addGetInstanceFunction("Element");
        $this->handler->whitelistFunction('Element', 'getInstance', 'getAsJSONString');
        $this->handler->setUp(new NullAJAXServerImpl(), "Element");
        $list = $this->handler->listFunctions("Element");
        $this->assertContains("getInstance", $list);
        /** @var JSONFunction $function */
        $function = $this->parser->parseString("Element.getInstance()")->toJSONProgram();
        $r = $this->handler->handle("Element", $function, $this);
        $this->assertTrue($this === $r);
    }

    public function testCustomFunctionNotWhiteListedIsNotOk()
    {
        $this->handler->addGetInstanceFunction("Element");
        $this->handler->whitelistFunction('Element', 'getAsJSONString');
        $this->handler->setUp(new NullAJAXServerImpl(), "Element");
        $list = $this->handler->listFunctions("Element");
        $this->assertNotContains("getInstance", $list);

    }


    public function testStringOfActualTypeDoesAddTypesAndFunctions()
    {
        $handler = new GenericObjectTypeHandlerImpl("ChristianBudde\\Part\\controller\\json\\Object");
        $this->assertTrue($handler->hasType("Element"));

        $handler = new GenericObjectTypeHandlerImpl("ChristianBudde\\Part\\controller\\json\\Object");
        $this->assertTrue($handler->hasType("Element"));


    }

    public function testSetUpBogusElementIsOk()
    {
        $handler = new GenericObjectTypeHandlerImpl("NonExistingObject");
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
        $f = $this->parser->parseString("Element.custom2()")->toJSONProgram();
        $this->assertTrue($this->handler->handle('Element', $f));


    }


    public function testCanNotHandleWithWrongNumberOfArguments()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function ($element, array $a) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element . custom()")->toJSONProgram();
        $this->assertFalse($this->handler->canHandle('Element', $f));
    }


    public function testCanHandleWithSomeArgumentsBeingOptional()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function ($element, array $a = []) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element . custom()")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));
    }


    public function testCanHandleWithSomeArgumentsBeingOptionalAndTyped()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function ($element, Object $a = null) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element . custom()")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));
    }

    public function testOptionalTypedArgumentCanBeNull()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function ($element, Object $a = null) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element . custom(null)")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));
    }

    public function testOptionalTypedArgumentNotLastCanBeNull()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function ($element, Object $a = null, $s) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element . custom(null,'some string')")->toJSONProgram();
        $this->assertTrue($this->handler->canHandle('Element', $f));
    }

    public function testCanHandleWithSomeMiddleArgumentsBeingOptional()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function ($element, array $a = [], $v) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element . custom([])")->toJSONProgram();
        $this->assertFalse($this->handler->canHandle('Element', $f));
    }


    public function testCanNotHandleWithWrongArguments()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function ($element, array $a) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element . custom('string')")->toJSONProgram();
        $this->assertFalse($this->handler->canHandle('Element', $f));
    }

    public function testCanNotHandleWithWrongArgumentsType()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function ($element, Page $a) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element . custom('string')")->toJSONProgram();
        $this->assertFalse($this->handler->canHandle('Element', $f));
    }

    public function testCanHandleWhenInputIsConverted()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function ($element, Object $a) {
        });
        $this->handler->addFunctionPreCallFunction('Element', 'custom', function($type, $instance, $functionName, &$arguments){
            $arguments[0] = new ObjectImpl('someName');
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element . custom('string')")->toJSONProgram();


        $this->assertTrue($this->handler->canHandle('Element', $f));
    }

    public function testCanNotHandleWithWrongNullToTypedArgument()
    {
        $this->setUpHandler($this->handler);
        $this->handler->addFunction('Element', 'custom', function ($element, Page $a) {
        });
        /** @var JSONFunction $f */
        $f = $this->parser->parseString("Element . custom(null)")->toJSONProgram();
        $this->assertFalse($this->handler->canHandle('Element', $f));
    }

    public function testCanCallFunctionOnObjectWithoutFunctions()
    {

        $handler = new GenericObjectTypeHandlerImpl("JSONProgram");
        $this->setUpHandler($handler);
        $handler->addFunction('JSONProgram', 'custom', function (array $a) {
        });
        /** @var \ChristianBudde\Part\controller\json\JSONFunction $f */
        $f = $this->parser->parseString("JSONProgram . custom([])")->toJSONProgram();
        $this->assertTrue($handler->canHandle('JSONProgram', $f));
    }


    public function testCanAddAlias()
    {
        $handler = new GenericObjectTypeHandlerImpl("JSONProgram");
        $handler->addAlias('ProgramAlias', ['JSONProgram']);
        $this->setUpHandler($handler);
        $handler->addFunction('JSONProgram', 'custom', function () {
            return 1;
        });

        /** @var \ChristianBudde\Part\controller\json\JSONFunction $f */
        $f = $this->parser->parseString("ProgramAlias . custom()")->toJSONProgram();
        $this->assertTrue($handler->canHandle('ProgramAlias', $f));
        $this->assertEquals(1, $handler->handle('ProgramAlias', $f));
    }

     public function testWillNotOverwriteAlias()
    {
        $handler = new GenericObjectTypeHandlerImpl("JSONProgram");
        $handler->addAlias('ProgramAlias', ['JSONProgram']);
        $handler->addAlias('ProgramAlias', ['DifferentType']);
        $this->setUpHandler($handler);
        $handler->addFunction('JSONProgram', 'custom', function () {
            return 1;
        });

        /** @var \ChristianBudde\Part\controller\json\JSONFunction $f */
        $f = $this->parser->parseString("ProgramAlias . custom()")->toJSONProgram();
        $this->assertTrue($handler->canHandle('ProgramAlias', $f));
        $this->assertEquals(1, $handler->handle('ProgramAlias', $f));
    }

     public function testAddedAliasIsInTypes()
    {
        $handler = new GenericObjectTypeHandlerImpl("JSONProgram");
        $handler->addAlias('ProgramAlias', ['JSONProgram']);
        $handler->addFunction('JSONProgram', 'custom', function () {
            return 1;
        });
        $this->assertTrue(in_array('ProgramAlias',$handler->listTypes()));
        $this->assertTrue(in_array('custom',$handler->listFunctions('ProgramAlias')));
    }

     public function testAddedAliasIsOnceInTypes()
     {
         $handler = new GenericObjectTypeHandlerImpl("JSONProgram");
         $handler->addAlias('ProgramAlias', ['JSONProgram']);
         $handler->addAlias('ProgramAlias', ['JSONProgram']);
         $handler->addFunction('JSONProgram', 'custom', function () {
             return 1;
         });

         $this->assertEquals(1, array_count_values($handler->listTypes())['ProgramAlias']);

     }








    private function setUpHandler(TypeHandler $handler)
    {
        foreach ($handler->listTypes() as $type) {
            $handler->setUp($this->nullAJAXServer, $type);
        }
    }


}