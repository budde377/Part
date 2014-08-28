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
    private $handler;

    protected function setUp()
    {
        $this->backendContainer = new StubBackendSingletonContainerImpl();
        $this->backendContainer->setConfigInstance($this->config = new StubConfigImpl());
        $this->server = new AJAXServerImpl($this->backendContainer);
        $this->handler = new StubAJAXTypeHandlerImpl();
    }


    public function testRegisterHandlerDoCallSetup()
    {
        $this->server->registerHandler($this->handler);
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('setUp', $this->handler));
        $this->assertNull($r['arguments'][1]);
    }

    public function testRegisterHandlerCallsSetUp()
    {
        $this->handler->types = [$t = 'someType'];
        $this->server->registerHandler($this->handler);
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('setUp', $this->handler));
        $this->assertEquals($t, $r['arguments'][1]);

    }


    public function testRegisterHandlerCallsSetUpForEachType()
    {
        $this->handler->types = [$t1 = 'someType', $t2 = 'someOtherType'];
        $this->server->registerHandler($this->handler);
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('setUp', $this->handler));
        $this->assertEquals($t1, $r['arguments'][1]);
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('setUp', $this->handler));
        $this->assertEquals($t2, $r['arguments'][1]);
    }


    public function testRegisterFromConfigWillRegister()
    {
        $this->config->setAJAXTypeHandlers([
            ['class_name' => 'StubAJAXTypeHandlerImpl'],
            ['class_name' => 'StubAJAXTypeHandlerImpl', 'path' => 'stubs/StubAJAXTypeHandlerImpl.php']
        ]);
        $this->server->registerHandlersFromConfig();

        $this->assertEquals(3, count($_SESSION['type_handlers']));
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('__construct', $_SESSION['type_handlers'][1]));
        $this->assertEquals($this->backendContainer, $r['arguments'][0]);
        $this->assertNotNull($this->checkIfFunctionIsCalled('setUp', $_SESSION['type_handlers'][1]));
        $this->assertNotNull($r = $this->checkIfFunctionIsCalled('__construct', $_SESSION['type_handlers'][2]));
        $this->assertEquals($this->backendContainer, $r['arguments'][0]);
        $this->assertNotNull($this->checkIfFunctionIsCalled('setUp', $_SESSION['type_handlers'][2]));

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
            ['class_name' => 'NotARealClassName', 'path' => 'stubs/StubAJAXTypeHandlerImpl.php']
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


    protected function tearDown()
    {
        parent::tearDown();
        unset($_SESSION['type_handlers']);
    }

} 