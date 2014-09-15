<?php

namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\ajax\AJAXServerImpl;
use ChristianBudde\cbweb\controller\function_string\FunctionStringParserImpl;
use ChristianBudde\cbweb\controller\json\JSONObjectImpl;
use ChristianBudde\cbweb\controller\json\JSONResponse;
use ChristianBudde\cbweb\controller\json\JSONResponseImpl;
use ChristianBudde\cbweb\controller\json\JSONFunction;
use ChristianBudde\cbweb\controller\json\JSONFunctionImpl;
use ChristianBudde\cbweb\controller\json\JSONTypeImpl;
use ChristianBudde\cbweb\test\stub\NullPageElementImpl;
use PHPUnit_Framework_TestCase;
use ChristianBudde\cbweb\test\stub\StubAJAXTypeHandlerImpl;
use ChristianBudde\cbweb\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\cbweb\test\stub\StubConfigImpl;
use ChristianBudde\cbweb\test\stub\StubPageContentImpl;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 3:45 PM
 */
class AJAXServerImplTest extends PHPUnit_Framework_TestCase
{
    /** @var  \ChristianBudde\cbweb\controller\ajax\AJAXServerImpl */
    private $server;
    /** @var  StubConfigImpl */
    private $config;
    private $backendContainer;
    /** @var  \ChristianBudde\cbweb\test\stub\StubAJAXTypeHandlerImpl */
    private $handler1;
    private $handler2;
    /** @var  FunctionStringParserImpl */
    private $functionStringParser;

    protected function setUp()
    {
        $this->backendContainer = new StubBackendSingletonContainerImpl();
        $this->backendContainer->setConfigInstance($this->config = new StubConfigImpl());
        $this->server = new AJAXServerImpl($this->backendContainer);
        $this->handler1 = new StubAJAXTypeHandlerImpl();
        $this->handler2 = new StubAJAXTypeHandlerImpl();
        $this->functionStringParser = new FunctionStringParserImpl();
    }


    public function testRegisterHandlerDoCallSetup()
    {
        $this->server->registerHandler($this->handler1);
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('setUp', $this->handler1));
        $this->assertNull($r['arguments'][1]);
    }

    public function testRegisterHandlerCallsSetUp()
    {
        $this->handler1->types = [$t = 'someType'];
        $this->server->registerHandler($this->handler1);
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('setUp', $this->handler1));
        $this->assertEquals($t, $r['arguments'][1]);

    }


    public function testRegisterHandlerCallsSetUpForEachType()
    {
        $this->handler1->types = [$t1 = 'someType', $t2 = 'someOtherType'];
        $this->server->registerHandler($this->handler1);
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('setUp', $this->handler1));
        $this->assertEquals($t1, $r['arguments'][1]);
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('setUp', $this->handler1));
        $this->assertEquals($t2, $r['arguments'][1]);
    }


    public function testRegisterFromConfigWillRegister()
    {
        $this->config->setAJAXTypeHandlers([
            ['class_name' => 'StubAJAXTypeHandlerImpl'],
            ['class_name' => 'StubAJAXTypeHandlerImpl', 'path' => dirname(__FILE__) . '/stubs/StubAJAXTypeHandlerImpl.php']
        ]);
        $this->server->registerHandlersFromConfig();

        $this->assertEquals(4, count($_SESSION['type_handlers']));
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('__construct', $_SESSION['type_handlers'][2]));
        $this->assertEquals($this->backendContainer, $r['arguments'][0]);
        $this->assertNotNull($this->checkIfFunctionIsCalled('setUp', $_SESSION['type_handlers'][2]));
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('__construct', $_SESSION['type_handlers'][3]));
        $this->assertEquals($this->backendContainer, $r['arguments'][0]);
        $this->assertNotNull($this->checkIfFunctionIsCalled('setUp', $_SESSION['type_handlers'][3]));

    }

    public function testRegisterFromConfigWithWrongLinkWillThrowException()
    {
        $this->config->setAJAXTypeHandlers([
            ['class_name' => 'StubAJAXTypeHandlerImpl', 'path' => '_stub/notarealink.php']
        ]);

        $this->setExpectedException('ChristianBudde\cbweb\FileNotFoundException');
        $this->server->registerHandlersFromConfig();

    }

    public function testRegisterFromConfigWithNonExistingClassNameWillThrowException()
    {
        $this->config->setAJAXTypeHandlers([
            ['class_name' => 'NotARealClassName', 'path' => dirname(__FILE__) . '/stubs/StubAJAXTypeHandlerImpl.php']
        ]);

        $this->setExpectedException('ChristianBudde\cbweb\ClassNotDefinedException');
        $this->server->registerHandlersFromConfig();

    }

    public function testRegisterFromConfigWithWrongInstanceThrowsException()
    {
        $this->config->setAJAXTypeHandlers([
            ['class_name' => 'NullOptimizerImpl']
        ]);

        $this->setExpectedException('ChristianBudde\cbweb\ClassNotInstanceOfException');
        $this->server->registerHandlersFromConfig();

    }

    public function testHandleOnNonJSONFunctionReturnsErrorResponse()
    {
        $this->handler1->types = ['someType'];
        $this->server->registerHandler($this->handler1);
        /** @var \ChristianBudde\cbweb\controller\json\JSONResponse $r */
        $r = $this->server->handleFromJSONString((new JSONObjectImpl('someType'))->getAsJSONString());
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertEquals(JSONResponse::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(JSONResponse::ERROR_CODE_MALFORMED_REQUEST, $r->getErrorCode());
    }

    public function testHandleOnJSONFunctionReturnsCallsAppropriateHandler()
    {
        $type = 'someType';
        $this->handler1->types = [$type];
        $this->handler1->canHandle[$type] = false;

        $this->handler2->types = [$type];
        $this->handler2->canHandle[$type] = true;
        $this->handler2->handle[$type] = 'success';

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);
        $func = new JSONFunctionImpl('func', new JSONTypeImpl($type));
        /** @var JSONResponse $r */
        $r = $this->server->handleFromJSONString($func->getAsJSONString());
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('canHandle', $this->handler2));
        $this->assertEquals($r1, $r2);
        $this->assertEquals([$type, $func, null], $r1['arguments']);

        $this->assertNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler2));
        $this->assertEquals([$type, $func, null], $r2['arguments']);

        $this->assertEquals('success', $r->getPayload());

    }


    public function testHandleOnJSONFunctionReturnsCallsAppropriateHandlerFromString()
    {
        $type = 'someType';
        $this->handler1->types = [$type];
        $this->handler1->canHandle[$type] = false;

        $this->handler2->types = [$type];
        $this->handler2->canHandle[$type] = true;
        $this->handler2->handle[$type] = 'success';

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);
        $funcString = "someType.func()";
        $func = new JSONFunctionImpl('func', new JSONTypeImpl($type));
        /** @var \ChristianBudde\cbweb\controller\json\JSONResponse $r */
        $r = $this->server->handleFromFunctionString($funcString);
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('canHandle', $this->handler2));
        $this->assertEquals($r1, $r2);
        $this->assertEquals([$type, $func, null], $r1['arguments']);

        $this->assertNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler2));
        $this->assertEquals([$type, $func, null], $r2['arguments']);

        $this->assertEquals('success', $r->getPayload());
    }

    public function testHandleOnJSONFunctionReturnsCallsAppropriateHandlerFromStringWithArguments()
    {
        $type = 'someType';
        $this->handler1->types = [$type];
        $this->handler1->canHandle[$type] = false;

        $this->handler2->types = [$type];
        $this->handler2->canHandle[$type] = true;
        $this->handler2->handle[$type] = 'success';

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);
        $funcString = "someType.func('asdasd','asdasdasdasd',123)";
        $func = new JSONFunctionImpl('func', new JSONTypeImpl($type));
        $func->setArg(0, 'asdasd');
        $func->setArg(1, 'asdasdasdasd');
        $func->setArg(2, 123);
        /** @var JSONResponse $r */
        $r = $this->server->handleFromFunctionString($funcString);
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('canHandle', $this->handler2));
        $this->assertEquals($r1, $r2);
        $this->assertEquals([$type, $func, null], $r1['arguments']);

        $this->assertNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler2));
        $this->assertEquals([$type, $func, null], $r2['arguments']);

        $this->assertEquals('success', $r->getPayload());
    }

    public function testHandleOnJSONFunctionReturnsCallsAppropriateHandlerWithNestedFunctions()
    {

        $type = 'someType';

        $this->handler2->types = [$type];
        $this->handler2->canHandle[$type] = true;
        $this->handler2->handle[$type] = 'success';

        $this->server->registerHandler($this->handler2);

        $func = new JSONFunctionImpl('func', new JSONTypeImpl($type . '2'));
        /** @var \ChristianBudde\cbweb\controller\json\JSONResponse $r */
        $r = $this->server->handleFromJSONString($func->getAsJSONString());
        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler2));
        $this->assertNull($this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertEquals(JSONResponse::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(JSONResponse::ERROR_CODE_NO_SUCH_FUNCTION, $r->getErrorCode());
    }

    public function testHandleOnNestedFunctionsIsOk()
    {


        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = new NullPageElementImpl();


        $type2 = 'ChristianBudde\cbweb\PageElement';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = 'success';

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        $func2 = new JSONFunctionImpl('func2', $func1 = new JSONFunctionImpl('func', new JSONTypeImpl($type1)));
        /** @var JSONResponse $r */
        $r = $this->server->handleFromJSONString($func2->getAsJSONString());
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('canHandle', $this->handler2));
        $this->assertNotEquals($r1, $r2);

        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler2));
        $this->assertEquals([$type1, $func1, null], $r1['arguments']);
        $this->assertEquals([$type2, $func2, $instance1], $r2['arguments']);

        $this->assertEquals('success', $r->getPayload());
    }

    public function testHandleOnFunctionsInArgumentsIsOk()
    {


        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = "success too";


        $type2 = 'PageElement';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = 'success';

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        /** @var JSONResponse $r */
        $r = $this->server->handleFromFunctionString('SomeElement.func(PageElement.f())');
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('canHandle', $this->handler2));
        $this->assertNotEquals($r1, $r2);

        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler2));
        $this->functionStringParser->parseFunctionCall("SomeElement.func('success')", $func1);
        $this->functionStringParser->parseFunctionCall("PageElement.f()", $func2);
        $this->assertEquals([$type1, $func1, null], $r1['arguments']);
        $this->assertEquals([$type2, $func2, null], $r2['arguments']);

        $this->assertEquals("success too", $r->getPayload());
    }

    public function testHandleOnFunctionsInArgumentReturnsFirstResponse()
    {


        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = new NullPageElementImpl();


        $type2 = 'PageElement';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $expectedResponse = ($this->handler2->handle[$type2] = new JSONResponseImpl());

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        /** @var \ChristianBudde\cbweb\controller\json\JSONResponse $r */
        $r = $this->server->handleFromFunctionString('SomeElement.func(PageElement.f())');
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('canHandle', $this->handler2));

        $this->assertNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler2));


        $this->assertEquals($expectedResponse, $r);
    }


    public function testHandleOnNestedFunctionsWithNullReturnedIsNull()
    {


        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = null;


        $type2 = 'PageElement';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = 'success';

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        $func2 = new JSONFunctionImpl('func2', $func1 = new JSONFunctionImpl('func', new JSONTypeImpl($type1)));
        /** @var \ChristianBudde\cbweb\controller\json\JSONResponse $r */
        $r = $this->server->handleFromJSONString($func2->getAsJSONString());
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler2));

        $this->assertNotNull($this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('handle', $this->handler2));

        $this->assertEquals(JSONResponse::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(JSONResponse::ERROR_CODE_NO_SUCH_FUNCTION, $r->getErrorCode());
    }

    public function testHandleOnNestedFunctionsWithJSONResponseReturnedIsResponse()
    {


        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $expectedResponse = ($this->handler1->handle[$type1] = $instance1 = new JSONResponseImpl());


        $type2 = 'PageElement';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = 'success';

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        $func2 = new JSONFunctionImpl('func2', $func1 = new JSONFunctionImpl('func', new JSONTypeImpl($type1)));
        /** @var \ChristianBudde\cbweb\controller\json\JSONResponse $r */
        $r = $this->server->handleFromJSONString($func2->getAsJSONString());
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler2));

        $this->assertNotNull($this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('handle', $this->handler2));

        $this->assertEquals($expectedResponse, $r);

    }

    public function testHandleOnCompositeFunctionsWithJSONResponseReturnedIsResponse()
    {


        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $expectedResponse = ($this->handler1->handle[$type1] = $instance1 = new JSONResponseImpl());


        $type2 = 'JSONResponse';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = 'success';

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        /** @var JSONResponse $r */
        $r = $this->server->handleFromFunctionString("SomeElement.func().f()..f1()..f2()");
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler2));

        $this->assertNotNull($this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('handle', $this->handler2));

        $this->assertEquals($expectedResponse, $r);

    }


    public function testDoNotWrapJSONResponse()
    {
        $type = 'someType';
        $this->handler2->types = [$type];
        $this->handler2->canHandle[$type] = true;
        $expectedResponse = ($this->handler2->handle[$type] = new JSONResponseImpl());
        $expectedResponse->setPayload("success");
        $this->server->registerHandler($this->handler2);
        $func = new JSONFunctionImpl('func', new JSONTypeImpl($type));
        /** @var \ChristianBudde\cbweb\controller\json\JSONResponse $r */
        $r = $this->server->handleFromJSONString($func->getAsJSONString());

        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler2));
        $this->assertEquals($expectedResponse, $r);
        $this->assertNull($r->getID());
    }

    public function testIdIsCorrectlySetInResponse()
    {
        $type = 'someType';
        $this->handler2->types = [$type];
        $this->handler2->canHandle[$type] = true;
        $expectedResponse = ($this->handler2->handle[$type] = new JSONResponseImpl());
        $expectedResponse->setPayload("success");
        $this->server->registerHandler($this->handler2);
        $this->functionStringParser->parseFunctionCall('someType.func()', $func);

        /** @var JSONFunction $func */
        /** @var JSONResponse $r */
        $func->setId($id = 1337);

        $r = $this->server->handleFromJSONString($func->getAsJSONString());

        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler2));
        $this->assertEquals($id, $r->getID());
    }


    private function checkIfFunctionIsCalled($functionName, StubAJAXTypeHandlerImpl $handler)
    {
        foreach ($handler->calledMethods as $k => $v) {
            if ($v['method'] == $functionName) {
                unset($handler->calledMethods[$k]);
                return $v;
            }
        }
        return null;
    }

    public function testCompositeFunctionsAreOk()
    {


        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = "success";


        $this->server->registerHandler($this->handler1);

        /** @var \ChristianBudde\cbweb\controller\json\JSONResponse $r */
        $r = $this->server->handleFromFunctionString('SomeElement..func()..func2()');
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertNotNull($this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler1));

        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('handle', $this->handler1));

        $this->functionStringParser->parseFunctionCall("SomeElement.func()", $func1);
        $this->functionStringParser->parseFunctionCall("SomeElement.func2()", $func2);

        $this->assertEquals([$type1, $func1, null], $r1['arguments']);
        $this->assertEquals([$type1, $func2, null], $r2['arguments']);

        $this->assertEquals("success", $r->getPayload());

    }

    public function testCompositeFunctionsOnFunctionChainsAreOk()
    {


        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = new NullPageElementImpl();


        $type2 = 'ChristianBudde\cbweb\PageElement';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = $instance2 = "success";

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        /** @var \ChristianBudde\cbweb\controller\json\JSONResponse $r */
        $r = $this->server->handleFromFunctionString('SomeElement.f()..f1()..f2()');
        $this->assertInstanceOf('ChristianBudde\cbweb\JSONResponse', $r);
        $this->assertNotNull($this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler1));

        $this->assertNotNull($this->checkIfFunctionIsCalled('canHandle', $this->handler2));
        $this->assertNotNull($this->checkIfFunctionIsCalled('canHandle', $this->handler2));
        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler2));

        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler2));
        $this->assertNotNull($r3 = $this->checkIfFunctionIsCalled('handle', $this->handler2));
        $this->assertNull($this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('handle', $this->handler2));

        $this->functionStringParser->parseFunctionCall("SomeElement.f()", $func1);
        $this->functionStringParser->parseFunctionCall("SomeElement.f().f1()", $func2);
        $this->functionStringParser->parseFunctionCall("SomeElement.f().f2()", $func3);

        $this->assertEquals([$type1, $func1, null], $r1['arguments']);
        $this->assertEquals([$type2, $func2, $instance1], $r2['arguments']);
        $this->assertEquals([$type2, $func3, $instance1], $r3['arguments']);

        $this->assertEquals("success", $r->getPayload());

    }


    public function testHandlerTypeFunctionRight()
    {

        $type1 = 'Content';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = "content";


        $type2 = 'ChristianBudde\cbweb\PageContent';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = $instance2 = "pageContent";

        $type3 = 'SomeElement';
        $handler3 = new StubAJAXTypeHandlerImpl();
        $handler3->types = [$type3];
        $handler3->canHandle[$type3] = true;
        $handler3->handle[$type3] = $instance3 = new StubPageContentImpl();

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);
        $this->server->registerHandler($handler3);

        $this->server->handleFromFunctionString("SomeElement.get().get()");

        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($this->checkIfFunctionIsCalled('canHandle', $this->handler2));

    }

    public function testFunctionReturningNullAsArgumentToAnotherFunctionIsNull()
    {
        $type1 = 'Content';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = null;
        $this->server->registerHandler($this->handler1);
        $this->server->handleFromFunctionString("Content.c1(Content.c2())");

        $this->assertNotNull($this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        /** @var JSONFunction $f */
        $f = $this->functionStringParser->parseFunctionString("Content.c1()");
        $f->setArg(0, null);
        $this->assertEquals([$type1, $this->functionStringParser->parseFunctionString("Content.c2()"), $instance1], $r1['arguments']);
        $this->assertEquals([$type1, $f, $instance1], $r2['arguments']);

    }


    protected function tearDown()
    {
        parent::tearDown();
        unset($_SESSION['type_handlers']);
    }

}