<?php

namespace ChristianBudde\Part\test;

use ChristianBudde\Part\controller\ajax\ServerImpl;
use ChristianBudde\Part\controller\function_string\Parser;
use ChristianBudde\Part\controller\function_string\ParserImpl;
use ChristianBudde\Part\controller\json\JSONFunction;
use ChristianBudde\Part\controller\json\JSONFunctionImpl;
use ChristianBudde\Part\controller\json\ObjectImpl;
use ChristianBudde\Part\controller\json\Response;
use ChristianBudde\Part\controller\json\ResponseImpl;
use ChristianBudde\Part\controller\json\TypeImpl;
use ChristianBudde\Part\exception\FileNotFoundException;
use ChristianBudde\Part\test\stub\NullPageElementImpl;
use ChristianBudde\Part\test\stub\StubAJAXTypeHandlerGeneratorImpl;
use ChristianBudde\Part\test\stub\StubAJAXTypeHandlerImpl;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubConfigImpl;
use ChristianBudde\Part\test\stub\StubPageContentImpl;
use ChristianBudde\Part\test\stub\StubPageImpl;
use ChristianBudde\Part\test\stub\StubUserLibraryImpl;
use PHPUnit_Framework_TestCase;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 3:45 PM
 */
class AJAXServerImplTest extends PHPUnit_Framework_TestCase
{
    /** @var  \ChristianBudde\Part\controller\ajax\ServerImpl */
    private $server;
    /** @var  StubConfigImpl */
    private $config;
    private $backendContainer;
    /** @var  \ChristianBudde\Part\test\stub\StubAJAXTypeHandlerImpl */
    private $handler1;
    private $handler2;
    /** @var  StubUserLibraryImpl */
    private $userLibrary;
    /** @var  Parser */
    private $parser;

    protected function setUp()
    {
        $this->backendContainer = new StubBackendSingletonContainerImpl();
        $this->backendContainer->setConfigInstance($this->config = new StubConfigImpl());
        $this->backendContainer->setUserLibraryInstance($this->userLibrary = new StubUserLibraryImpl());
        $this->server = new ServerImpl($this->backendContainer);
        $this->handler1 = new StubAJAXTypeHandlerImpl();
        $this->handler2 = new StubAJAXTypeHandlerImpl();
        $this->parser = new ParserImpl();
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
            ['class_name' => 'ChristianBudde\Part\test\stub\StubAJAXTypeHandlerImpl'],
            ['class_name' => 'ChristianBudde\Part\test\stub\StubAJAXTypeHandlerImpl', 'path' => dirname(__FILE__) . '/stub/StubAJAXTypeHandlerImpl.php']
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


    public function testRegisterTypeHandlerGeneratorWillRegisterGenerated()
    {
        $this->config->setAJAXTypeHandlers([
            ['class_name' => 'ChristianBudde\Part\test\stub\StubAJAXTypeHandlerGeneratorImpl'],
            ['class_name' => 'ChristianBudde\Part\test\stub\StubAJAXTypeHandlerGeneratorImpl', 'path' => dirname(__FILE__) . '/stub/StubAJAXTypeHandlerImpl.php']
        ]);
        StubAJAXTypeHandlerGeneratorImpl::setHandler(new StubAJAXTypeHandlerImpl(1,2,3));
        $this->server->registerHandlersFromConfig();


        $this->assertEquals(3, count($_SESSION['type_handlers']));
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('__construct', $_SESSION['type_handlers'][2]));
        $this->assertEquals([1,2,3], $r['arguments']);
        $this->assertNotNull($this->checkIfFunctionIsCalled('setUp', $_SESSION['type_handlers'][2]));

    }

    public function testRegisterFromConfigWithWrongLinkWillThrowException()
    {
        $this->config->setAJAXTypeHandlers([
            ['class_name' => 'ChristianBudde\Part\test\stub\StubAJAXTypeHandlerImpl', 'path' => $fn = '_stub/notarealink.php']
        ]);


        try{
            $this->server->registerHandlersFromConfig();
        } catch(FileNotFoundException $e){
            $this->assertEquals($fn, $e->getFileName());
            $this->assertEquals( "AJAXTypeHandler class file", $e->getFileDesc());
        }

    }

    public function testRegisterFromConfigWithNonExistingClassNameWillThrowException()
    {
        $this->config->setAJAXTypeHandlers([
            ['class_name' => 'NotARealClassName', 'path' => dirname(__FILE__) . '/stub/StubAJAXTypeHandlerImpl.php']
        ]);

        $this->setExpectedException('ChristianBudde\Part\exception\ClassNotDefinedException');
        $this->server->registerHandlersFromConfig();

    }

    public function testRegisterFromConfigWithWrongInstanceThrowsException()
    {
        $this->config->setAJAXTypeHandlers([
            ['class_name' => 'ChristianBudde\Part\test\stub\NullOptimizerImpl']
        ]);

        $this->setExpectedException('ChristianBudde\Part\exception\ClassNotInstanceOfException');
        $this->server->registerHandlersFromConfig();

    }

    public function testHandleOnNonJSONFunctionReturnsErrorResponse()
    {
        $this->handler1->types = ['someType'];
        $this->server->registerHandler($this->handler1);
        /** @var \ChristianBudde\Part\controller\json\Response $r */
        $r = $this->server->handleFromJSONString((new ObjectImpl('someType'))->getAsJSONString());
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
        $this->assertEquals(Response::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(Response::ERROR_CODE_MALFORMED_REQUEST, $r->getErrorCode());

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
        $func = new JSONFunctionImpl('func', new TypeImpl($type));
        /** @var Response $r */
        $r = $this->server->handleFromJSONString($func->getAsJSONString());
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
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
        $funcString = "$type.func()";
        $func = new JSONFunctionImpl('func', new TypeImpl($type));
        /** @var \ChristianBudde\Part\controller\json\Response $r */
        $r = $this->server->handleFromFunctionString($funcString);
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('canHandle', $this->handler2));
        $this->assertEquals($r1, $r2);
        $this->assertEquals([$type, $func, null], $r1['arguments']);

        $this->assertNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler2));
        $this->assertEquals([$type, $func, null], $r2['arguments']);

        $this->assertEquals('success', $r->getPayload());
    }

    public function testAllCallsAreUnauthorizedIfTokenDoesNotVerify()
    {
        $this->userLibrary->verifyUserSessionToken = false;
        $r = $this->server->handleFromFunctionString("Target.f()");
        $this->assertEquals(new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_UNAUTHORIZED), $r);

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
        $func = new JSONFunctionImpl('func', new TypeImpl($type), ['asdasd', 'asdasdasdasd', 123]);
        /** @var Response $r */
        $r = $this->server->handleFromFunctionString($funcString);
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
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

        $func = new JSONFunctionImpl('func', new TypeImpl($type . '2'));
        /** @var \ChristianBudde\Part\controller\json\Response $r */
        $r = $this->server->handleFromJSONString($func->getAsJSONString());
        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler2));
        $this->assertNull($this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
        $this->assertEquals(Response::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(Response::ERROR_CODE_NO_SUCH_FUNCTION, $r->getErrorCode());
    }

    public function testWillSkipTypesWithoutHandler()
    {

        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = new StubPageImpl();

        $this->server->registerHandler($this->handler1);

        $func2 = new JSONFunctionImpl('func2', $func1 = new JSONFunctionImpl('func', new TypeImpl($type1)));
        /** @var Response $r */
        $r = $this->server->handleFromJSONString($func2->getAsJSONString());
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));

        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertEquals([$type1, $func1, null], $r1['arguments']);

        $this->assertEquals(new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_NO_SUCH_FUNCTION), $r);
    }


    public function testWillCallImplementedGenerator()
    {

        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = new StubPageImpl();

        $this->server->registerHandler($this->handler1);

        $func2 = new JSONFunctionImpl('func2', $func1 = new JSONFunctionImpl('func', new TypeImpl($type1)));
        /** @var Response $r */
        $r = $this->server->handleFromJSONString($func2->getAsJSONString());
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));

        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertEquals([$type1, $func1, null], $r1['arguments']);

        $this->assertEquals(new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_NO_SUCH_FUNCTION), $r);
    }



    public function testHandleOnNestedFunctionsIsOk()
    {


        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = new NullPageElementImpl();


        $type2 = 'ChristianBudde\Part\view\page_element\PageElement';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = 'success';

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        $func2 = new JSONFunctionImpl('func2', $func1 = new JSONFunctionImpl('func', new TypeImpl($type1)));
        /** @var Response $r */
        $r = $this->server->handleFromJSONString($func2->getAsJSONString());
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
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

        /** @var Response $r */
        $r = $this->server->handleFromFunctionString('SomeElement.func(PageElement.f())');
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('canHandle', $this->handler2));
        $this->assertNotEquals($r1, $r2);

        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler2));
        $this->assertEquals([$type1, $this->parser->parseString("SomeElement.func('success')")->toJSONProgram(), null], $r1['arguments']);
        $this->assertEquals([$type2, $this->parser->parseString("PageElement.f()")->toJSONProgram(), null], $r2['arguments']);

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
        $expectedResponse = ($this->handler2->handle[$type2] = new ResponseImpl());

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        /** @var \ChristianBudde\Part\controller\json\Response $r */
        $r = $this->server->handleFromFunctionString('SomeElement.func(PageElement.f())');
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
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

        $func2 = new JSONFunctionImpl('func2', $func1 = new JSONFunctionImpl('func', new TypeImpl($type1)));
        /** @var \ChristianBudde\Part\controller\json\Response $r */
        $r = $this->server->handleFromJSONString($func2->getAsJSONString());
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler2));

        $this->assertNotNull($this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('handle', $this->handler2));

        $this->assertEquals(Response::RESPONSE_TYPE_ERROR, $r->getResponseType());
        $this->assertEquals(Response::ERROR_CODE_NO_SUCH_FUNCTION, $r->getErrorCode());
    }

    public function testHandleOnNestedFunctionsWithJSONResponseReturnedIsResponse()
    {


        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $expectedResponse = ($this->handler1->handle[$type1] = $instance1 = new ResponseImpl());


        $type2 = 'PageElement';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = 'success';

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        $func2 = new JSONFunctionImpl('func2', $func1 = new JSONFunctionImpl('func', new TypeImpl($type1)));
        /** @var \ChristianBudde\Part\controller\json\Response $r */
        $r = $this->server->handleFromJSONString($func2->getAsJSONString());
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
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
        $expectedResponse = ($this->handler1->handle[$type1] = $instance1 = new ResponseImpl());


        $type2 = 'JSONResponse';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = 'success';

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        /** @var Response $r */
        $r = $this->server->handleFromFunctionString("SomeElement.func().f()..f1()..f2()");
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
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
        $expectedResponse = ($this->handler2->handle[$type] = new ResponseImpl());
        $expectedResponse->setPayload("success");
        $this->server->registerHandler($this->handler2);
        $func = new JSONFunctionImpl('func', new TypeImpl($type));
        /** @var \ChristianBudde\Part\controller\json\Response $r */
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
        $expectedResponse = ($this->handler2->handle[$type] = new ResponseImpl());
        $expectedResponse->setPayload("success");
        $this->server->registerHandler($this->handler2);
        $func = $this->parser->parseString('someType.func()')->toJSONProgram();

        /** @var JSONFunction $func */
        /** @var Response $r */
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

        /** @var \ChristianBudde\Part\controller\json\Response $r */
        $r = $this->server->handleFromFunctionString('SomeElement..func()..func2()');
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
        $this->assertNotNull($this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNotNull($this->checkIfFunctionIsCalled('canHandle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('canHandle', $this->handler1));

        $this->assertNotNull($r1 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNotNull($r2 = $this->checkIfFunctionIsCalled('handle', $this->handler1));
        $this->assertNull($this->checkIfFunctionIsCalled('handle', $this->handler1));

        $this->assertEquals([$type1, $this->parser->parseString("SomeElement.func()")->toJSONProgram(), null], $r1['arguments']);
        $this->assertEquals([$type1, $this->parser->parseString("SomeElement.func2()")->toJSONProgram(), null], $r2['arguments']);

        $this->assertEquals("success", $r->getPayload());

    }

    public function testCompositeFunctionsOnFunctionChainsAreOk()
    {


        $type1 = 'SomeElement';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = new NullPageElementImpl();


        $type2 = 'ChristianBudde\Part\view\page_element\PageElement';
        $this->handler2->types = [$type2];
        $this->handler2->canHandle[$type2] = true;
        $this->handler2->handle[$type2] = $instance2 = "success";

        $this->server->registerHandler($this->handler1);
        $this->server->registerHandler($this->handler2);

        /** @var \ChristianBudde\Part\controller\json\Response $r */
        $r = $this->server->handleFromFunctionString('SomeElement.f()..f1()..f2()');
        $this->assertInstanceOf('ChristianBudde\Part\controller\json\Response', $r);
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

        $this->assertEquals([$type1, $this->parser->parseString("SomeElement.f()")->toJSONProgram(), null], $r1['arguments']);
        $this->assertEquals([$type2, $this->parser->parseString("SomeElement.f().f1()")->toJSONProgram(), $instance1], $r2['arguments']);
        $this->assertEquals([$type2, $this->parser->parseString("SomeElement.f().f2()")->toJSONProgram(), $instance1], $r3['arguments']);

        $this->assertEquals("success", $r->getPayload());

    }


    public function testHandlerTypeFunctionRight()
    {

        $type1 = 'Content';
        $this->handler1->types = [$type1];
        $this->handler1->canHandle[$type1] = true;
        $this->handler1->handle[$type1] = $instance1 = "content";


        $type2 = 'ChristianBudde\Part\model\page\PageContent';
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
        $f = $this->parser->parseString("Content.c1()")->toJSONProgram();
        $f = new JSONFunctionImpl($f->getName(), $f->getTarget(), [null]);
        $this->assertEquals([$type1, $this->parser->parseString("Content.c2()")->toJSONProgram(), $instance1], $r1['arguments']);
        $this->assertEquals([$type1, $f, $instance1], $r2['arguments']);

    }


    protected function tearDown()
    {
        parent::tearDown();
        unset($_SESSION['type_handlers']);
    }

}