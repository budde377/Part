<?php


/**
 * User: budde
 * Date: 5/28/12
 * Time: 2:53 PM
 */
namespace ChristianBudde\Part;


use ChristianBudde\Part\exception\ClassNotInstanceOfException;

use ChristianBudde\Part\exception\ForceExitException;
use ChristianBudde\Part\exception\TaskHasRunException;
use Exception;
use PHPUnit_Framework_TestCase;
use SimpleXMLElement;

class SiteFactoryImplTest extends PHPUnit_Framework_TestCase
{
    /** @var $backFactory BackendSingletonContainer */
    private $backFactory;
    private $defaultOwner = /** @lang XML */
        "<siteInfo><domain name='test' extension='dk'/><owner name='Admin Jensen' mail='test@test.dk' username='asd' /></siteInfo>";
    /** @var  SiteFactoryImpl */
    private $factory;
    private $config;

    protected function setUp()
    {
        $this->backFactory = new NullBackendSingletonContainerImpl();
    }

    private function setupFactory($config = null)
    {

        if ($config == null) {
            $config = "<config>{$this->defaultOwner}</config>";
        }
        $configXML = simplexml_load_string($config);
        $this->config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->factory = new SiteFactoryImpl($this->config);

    }


    public function testBuildPreAndPostTaskWillReturnTaskChainOnEmptyConfig()
    {
        $this->setupFactory();

        $this->assertInstanceOf('ChristianBudde\Part\util\task\TaskQueue', $this->factory->buildPostTaskQueue($this->backFactory), 'The buildPostTaskChain return must be instance of TaskChain');
        $this->assertInstanceOf('ChristianBudde\Part\util\task\TaskQueue', $this->factory->buildPreTaskQueue($this->backFactory), 'The buildPreTaskChain return must be instance of TaskChain');

    }

    public function testBuildPreTaskWillReturnTaskChainWithTasksSpecifiedInConfig()
    {

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <preTasks>
        <class link='util/task/ExceptionStubTaskImpl.php'>ChristianBudde\Part\util\\task\ExceptionStubTaskImpl</class>
        </preTasks>
        </config>");
        $preTasks = $this->factory->buildPreTaskQueue($this->backFactory);

        $this->setExpectedException('ChristianBudde\Part\exception\TaskHasRunException');
        $preTasks->execute();

    }

    public function testBuildPreTaskWillReturnTaskChainWithTasksSpecifiedInConfigButNoLink()
    {

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <preTasks>
        <class>ChristianBudde\\Part\\util\\task\\ExceptionStubTaskImpl</class>
        </preTasks>
        </config>");
        $preTasks = $this->factory->buildPreTaskQueue($this->backFactory);


        $this->setExpectedException('ChristianBudde\Part\exception\TaskHasRunException');
        $preTasks->execute();

    }

    public function testBuildPreTaskWillThrowExceptionWithNotTaskInConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $this->setupFactory("
        <config>{$this->defaultOwner}
        <preTasks>
        <class link='view/page_element/NullPageElementImpl.php'>ChristianBudde\\Part\\view\\page_element\\NullPageElementImpl</class>
        </preTasks>
        </config>");

        $exceptionWasThrown = false;
        try {
            $this->factory->buildPreTaskQueue($this->backFactory);
        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\exception\ClassNotInstanceOfException', $exception, 'The wrong exception was thrown.');
            /** @var $exception \ChristianBudde\Part\exception\ClassNotInstanceOfException */
            $exceptionWasThrown = true;
            $this->assertEquals('ChristianBudde\Part\view\page_element\NullPageElementImpl', $exception->getClass(), 'Was not expected class');
            $this->assertEquals('Task', $exception->getExpectedInstance(), 'Was not expected instance');
        }
        $this->assertTrue($exceptionWasThrown, 'A exception was not thrown.');

    }


    public function testBuildPostTaskWillThrowExceptionWithNotTaskInConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $this->setupFactory("
        <config>{$this->defaultOwner}
        <postTasks>
        <class link='view/page_element/NullPageElementImpl.php'>ChristianBudde\\Part\\view\\page_element\\NullPageElementImpl</class>
        </postTasks>
        </config>");

        $exceptionWasThrown = false;
        try {
            $this->factory->buildPostTaskQueue($this->backFactory);
        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\exception\ClassNotInstanceOfException', $exception, 'The wrong exception was thrown.');
            /** @var $exception ClassNotInstanceOfException */
            $exceptionWasThrown = true;
            $this->assertEquals('ChristianBudde\Part\view\page_element\NullPageElementImpl', $exception->getClass(), 'Was not expected class');
            $this->assertEquals('Task', $exception->getExpectedInstance(), 'Was not expected instance');
        }
        $this->assertTrue($exceptionWasThrown, 'A exception was not thrown.');

    }

    public function testBuildPostTaskWillReturnTaskChainWithTasksSpecifiedInConfig()
    {

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <postTasks>
        <class link='util/task/ExceptionStubTaskImpl.php'>ChristianBudde\\Part\\util\\task\\ExceptionStubTaskImpl</class>
        </postTasks>
        </config>");
        $postTasks = $this->factory->buildPostTaskQueue($this->backFactory);

        $this->setExpectedException('ChristianBudde\\Part\\exception\\TaskHasRunException');
        $postTasks->execute('PostTask', null);

    }

    public function testBuildPostTaskWillReturnTaskChainWithTasksSpecifiedInConfigButNoLink()
    {

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <postTasks>
        <class>ChristianBudde\\Part\\util\\task\\ExceptionStubTaskImpl</class>
        </postTasks>
        </config>");
        $postTasks = $this->factory->buildPostTaskQueue($this->backFactory);

        $this->setExpectedException('ChristianBudde\\Part\\exception\\TaskHasRunException');
        $postTasks->execute('PostTask', null);

    }

    public function testBuildPreTaskWillThrowExceptionIfFileNotFound()
    {
        $this->setExpectedException('ChristianBudde\Part\exception\FileNotFoundException');

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <preTasks>
        <class link='stubs/ThisFileIsNotFound.php'>ChristianBudde\\Part\\test\\stub\\TaskExceptionStubImpl</class>
        </preTasks>
        </config>");
        $this->factory->buildPreTaskQueue($this->backFactory);

    }

    public function testBuildPreTaskWillThrowExceptionIfTaskClassIsNotDefined()
    {
        $this->setExpectedException('ChristianBudde\Part\exception\ClassNotDefinedException');

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <preTasks>
        <class link='util/task/ExceptionStubTaskImpl.php'>WrongClassName</class>
        </preTasks>
        </config>");
        $this->factory->buildPreTaskQueue($this->backFactory);

    }

    public function testBuildPreTaskWillGiveRightArgumentToConstructor()
    {
        $this->setupFactory("
        <config>{$this->defaultOwner}
        <preTasks>
        <class>ChristianBudde\\Part\\util\\task\\ConstructorStubTaskImpl</class>
        </preTasks>
        </config>");
        try {
            $this->factory->buildPreTaskQueue($this->backFactory);
        } catch (ForceExitException $e) {
            $this->assertTrue($e->data[0] === $this->backFactory);
        }

    }

    public function testBuildPostTaskWillGiveRightArgumentToConstructor()
    {
        $this->setupFactory("
        <config>{$this->defaultOwner}
        <postTasks>
        <class>ChristianBudde\\Part\\util\\task\\ConstructorStubTaskImpl</class>
        </postTasks>
        </config>");
        try {
            $this->factory->buildPostTaskQueue($this->backFactory);
        } catch (ForceExitException $e) {
            $this->assertTrue($e->data[0] === $this->backFactory);
        }

    }

    public function testBuildPostTaskWillThrowExceptionIfFileNotFound()
    {

        $this->setExpectedException('ChristianBudde\Part\exception\FileNotFoundException');

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <postTasks>
        <class link='stubs/ThisFileIsNotFound.php'>TaskExceptionStubImpl</class>
        </postTasks>
        </config>");
        $this->factory->buildPostTaskQueue($this->backFactory);

    }

    public function testBuildPostTaskWillThrowExceptionIfTaskClassIsNotDefined()
    {
        $this->setExpectedException('ChristianBudde\Part\exception\ClassNotDefinedException');
        $this->setupFactory("
        <config>{$this->defaultOwner}
        <postTasks>
        <class link='util/task/ExceptionStubTaskImpl.php'>WrongClassName</class>
        </postTasks>
        </config>");
        $this->factory->buildPostTaskQueue($this->backFactory);

    }

    public function testBuildConfigWillReturnInstanceOfConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $this->setupFactory("
        <config>{$this->defaultOwner}
        </config>");
        $retConfig = $this->factory->buildConfig();
        $this->assertInstanceOf('ChristianBudde\Part\Config', $retConfig, 'Did not return Config');
    }

    public function testBuildConfigWillReturnNewInstanceOfConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $this->setupFactory("
        <config>{$this->defaultOwner}
        </config>");
        $retConfig = $this->factory->buildConfig();
        $secRetConfig = $this->factory->buildConfig();
        $this->assertFalse(($this->config === $retConfig), 'Did not return a new instance of Config');
        $this->assertFalse(($retConfig === $secRetConfig), 'Did not return a new instance of Config');
    }

    public function testBuildBackendSingletonContainerReturnNewInstanceOfThat()
    {
        /** @var $configXML SimpleXMLElement */
        $this->setupFactory("
        <config>{$this->defaultOwner}
        </config>");
        $ret = $this->factory->buildBackendSingletonContainer($this->config);
        $ret2 = $this->factory->buildBackendSingletonContainer($this->config);
        $this->assertFalse(($ret === $ret2), 'Did not return a new instance of container');
    }


}
