<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 3:45 PM
 */
class AJAXServerImplTest extends PHPUnit_Framework_TestCase
{
    /** @var  AJAXServerImpl */
    private $server;
    /** @var  StubConfigImpl */
    private $config;
    private $backendContainer;
    /** @var  StubAJAXTypeHandlerImpl */
    private $handler1;
    private $handler2;
    /** @var  FunctionStringParser */
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

        $this->setExpectedException('FileNotFoundException');
        $this->server->registerHandlersFromConfig();

    }

    public function testRegisterFromConfigWithNonExistingClassNameWillThrowException()
    {
        $this->config->setAJAXTypeHandlers([
            ['class_name' => 'NotARealClassName', 'path' => dirname(__FILE__) . '/stubs/StubAJAXTypeHandlerImpl.php']
        ]);

        $this->setExpectedException('ClassNotDefinedException');
        $this->server->registerHandlersFromConfig();

    }

    public function testRegisterFromConfigWithWrongInstanceThrowsException()
    {
        $this->config->setAJAXTypeHandlers([
            ['class_name' => 'NullOptimizerImpl']
        ]);

        $this->setExpectedException('ClassNotInstanceOfException');
        $this->server->registerHandlersFromConfig();

    }

    public function testHandleOnNonJSONFunctionReturnsErrorResponse()
    {
        $this->handler1->types = ['someType'];
        $this->server->registerHandler($this->handler1);
        /** @var JSONResponse $r */
        $r = $this->server->handleFromJSONString((new JSONObjectImpl('someType'))->getAsJSONString());
        $this->assertInstanceOf('JSONResponse', $r);
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
        $this->assertInstanceOf('JSONResponse', $r);
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
        /** @var JSONResponse $r */
        $r = $this->server->handleFromFunctionString($funcString);
        $this->assertInstanceOf('JSONResponse', $r);
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
        $this->assertInstanceOf('JSONResponse', $r);
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
        /** @var JSONResponse $r */
        $r = $this->server->handleFromJSONString($func->getAsJSONString());
        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler2));
        $this->assertNull($this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertInstanceOf('JSONResponse', $r);
        $this->assertEquals(JSONResponse::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(JSONResponse::ERROR_CODE_NO_SUCH_FUNCTION, $r->getErrorCode());
    }

    public function testHandleOnNestedFunctionsIsOk()
    {


        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = new NullPageElementImpl();


        $type2 = 'PageElement';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = 'success';

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        $func2 = new JSONFunctionImpl('func2', $func1 = new JSONFunctionImpl('func', new JSONTypeImpl($type1)));
        /** @var JSONResponse $r */
        $r = $this->server->handleFromJSONString($func2->getAsJSONString());
        $this->assertInstanceOf('JSONResponse', $r);
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
        $this->assertInstanceOf('JSONResponse', $r);
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

        /** @var JSONResponse $r */
        $r = $this->server->handleFromFunctionString('SomeElement.func(PageElement.f())');
        $this->assertInstanceOf('JSONResponse', $r);
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
        /** @var JSONResponse $r */
        $r = $this->server->handleFromJSONString($func2->getAsJSONString());
        $this->assertInstanceOf('JSONResponse', $r);
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
        /** @var JSONResponse $r */
        $r = $this->server->handleFromJSONString($func2->getAsJSONString());
        $this->assertInstanceOf('JSONResponse', $r);
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
        $this->assertInstanceOf('JSONResponse', $r);
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
        /** @var JSONResponse $r */
        $r = $this->server->handleFromJSONString($func->getAsJSONString());

        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler2));
        $this->assertEquals($expectedResponse, $r);
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

        /** @var JSONResponse $r */
        /** @var JSONFunction $func */
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

        /** @var JSONResponse $r */
        $r = $this->server->handleFromFunctionString('SomeElement..func()..func2()');
        $this->assertInstanceOf('JSONResponse', $r);
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


        $type2 = 'PageElement';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = $instance2 = "success";

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        /** @var JSONResponse $r */
        $r = $this->server->handleFromFunctionString('SomeElement.f()..f1()..f2()');
        $this->assertInstanceOf('JSONResponse', $r);
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


    protected function tearDown()
    {
        parent::tearDown();
        unset($_SESSION['type_handlers']);
    }

} 