<?php


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/28/12
 * Time: 2:53 PM
 * To change this template use File | Settings | File Templates.
 */
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\ConfigImpl;
use ChristianBudde\Part\exception\ClassNotInstanceOfException;
use ChristianBudde\Part\SiteFactoryImpl;
use ChristianBudde\Part\test\stub\ForceExitException;
use ChristianBudde\Part\test\stub\NullBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\ScriptHasRunException;
use Exception;
use PHPUnit_Framework_TestCase;
use SimpleXMLElement;

class SiteFactoryImplTest extends PHPUnit_Framework_TestCase
{
    /** @var $backFactory BackendSingletonContainer */
    private $backFactory;
    private $defaultOwner = "<siteInfo><domain name='test' extension='dk'/><owner name='Admin Jensen' mail='test@test.dk' username='asd' /></siteInfo>";

    protected function setUp()
    {
        $this->backFactory = new NullBackendSingletonContainerImpl();
    }

    public function testBuildPreAndPostScriptWillReturnScriptChainOnEmptyConfig()
    {
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);

        $this->assertInstanceOf('ChristianBudde\Part\util\script\ScriptChain', $factory->buildPostScriptChain($this->backFactory), 'The buildPostScriptChain return must be instance of ScriptChain');
        $this->assertInstanceOf('ChristianBudde\Part\util\script\ScriptChain', $factory->buildPreScriptChain($this->backFactory), 'The buildPreScriptChain return must be instance of ScriptChain');

    }

    public function testBuildPreScriptWillReturnScriptChainWithScriptsSpecifiedInConfig()
    {

        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <preScripts>
        <class link='stub/ExceptionStubScriptImpl.php'>ChristianBudde\Part\\test\stub\ExceptionStubScriptImpl</class>
        </preScripts>
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        $preScripts = $factory->buildPreScriptChain($this->backFactory);

        $exceptionWasThrown = false;
        try {
            $preScripts->run('PreScript', null);

        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\test\stub\ScriptHasRunException', $exception, 'The wrong exception was thrown.');
            /** @var $exception \ChristianBudde\Part\test\stub\ScriptHasRunException */
            $exceptionWasThrown = true;
            $this->assertEquals('PreScript', $exception->getName(), 'The Script ran with wrong name.');
            $this->assertNull($exception->getArgs(), 'Script ran with wrong argument');
        }
        $this->assertTrue($exceptionWasThrown, 'A exception was not thrown.');

    }

    public function testBuildPreScriptWillReturnScriptChainWithScriptsSpecifiedInConfigButNoLink()
    {

        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <preScripts>
        <class>ChristianBudde\\Part\\test\\stub\\ExceptionStubScriptImpl</class>
        </preScripts>
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        $preScripts = $factory->buildPreScriptChain($this->backFactory);

        $exceptionWasThrown = false;
        try {
            $preScripts->run('PreScript', null);

        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\test\stub\ScriptHasRunException', $exception, 'The wrong exception was thrown.');
            /** @var $exception ScriptHasRunException */
            $exceptionWasThrown = true;
            $this->assertEquals('PreScript', $exception->getName(), 'The Script ran with wrong name.');
            $this->assertNull($exception->getArgs(), 'Script ran with wrong argument');
        }
        $this->assertTrue($exceptionWasThrown, 'A exception was not thrown.');

    }

    public function testBuildPreScriptWillThrowExceptionWithNotScriptInConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <preScripts>
        <class link='stub/NullPageElementImpl.php'>ChristianBudde\\Part\\test\\stub\\NullPageElementImpl</class>
        </preScripts>
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);

        $exceptionWasThrown = false;
        try {
            $factory->buildPreScriptChain($this->backFactory);
        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\exception\ClassNotInstanceOfException', $exception, 'The wrong exception was thrown.');
            /** @var $exception \ChristianBudde\Part\exception\ClassNotInstanceOfException */
            $exceptionWasThrown = true;
            $this->assertEquals('ChristianBudde\Part\test\stub\NullPageElementImpl', $exception->getClass(), 'Was not expected class');
            $this->assertEquals('Script', $exception->getExpectedInstance(), 'Was not expected instance');
        }
        $this->assertTrue($exceptionWasThrown, 'A exception was not thrown.');

    }


    public function testBuildPostScriptWillThrowExceptionWithNotScriptInConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <postScripts>
        <class link='stub/NullPageElementImpl.php'>ChristianBudde\\Part\\test\\stub\\NullPageElementImpl</class>
        </postScripts>
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);

        $exceptionWasThrown = false;
        try {
            $factory->buildPostScriptChain($this->backFactory);
        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\exception\ClassNotInstanceOfException', $exception, 'The wrong exception was thrown.');
            /** @var $exception ClassNotInstanceOfException */
            $exceptionWasThrown = true;
            $this->assertEquals('ChristianBudde\Part\test\stub\NullPageElementImpl', $exception->getClass(), 'Was not expected class');
            $this->assertEquals('Script', $exception->getExpectedInstance(), 'Was not expected instance');
        }
        $this->assertTrue($exceptionWasThrown, 'A exception was not thrown.');

    }

    public function testBuildPostScriptWillReturnScriptChainWithScriptsSpecifiedInConfig()
    {

        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <postScripts>
        <class link='stub/ExceptionStubScriptImpl.php'>ChristianBudde\\Part\\test\\stub\\ExceptionStubScriptImpl</class>
        </postScripts>
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        $postScripts = $factory->buildPostScriptChain($this->backFactory);

        $this->setExpectedException('ChristianBudde\\Part\\test\\stub\\ScriptHasRunException');
        $postScripts->run('PostScript', null);

    }

    public function testBuildPostScriptWillReturnScriptChainWithScriptsSpecifiedInConfigButNoLink()
    {

        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <postScripts>
        <class>ChristianBudde\\Part\\test\\stub\\ExceptionStubScriptImpl</class>
        </postScripts>
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        $postScripts = $factory->buildPostScriptChain($this->backFactory);

        $this->setExpectedException('ChristianBudde\\Part\\test\\stub\\ScriptHasRunException');
        $postScripts->run('PostScript', null);

    }

    public function testBuildPreScriptWillThrowExceptionIfFileNotFound()
    {

        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <preScripts>
        <class link='stubs/ThisFileIsNotFound.php'>ChristianBudde\\Part\\test\\stub\\ScriptExceptionStubImpl</class>
        </preScripts>
        </config>");

        $this->setExpectedException('ChristianBudde\Part\exception\FileNotFoundException');

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        $factory->buildPreScriptChain($this->backFactory);

    }

    public function testBuildPreScriptWillThrowExceptionIfScriptClassIsNotDefined()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <preScripts>
        <class link='stub/ExceptionStubScriptImpl.php'>WrongClassName</class>
        </preScripts>
        </config>");

        $this->setExpectedException('ChristianBudde\Part\exception\ClassNotDefinedException');

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        $factory->buildPreScriptChain($this->backFactory);

    }

    public function testBuildPreScriptWillGiveRightArgumentToConstructor()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <preScripts>
        <class>ChristianBudde\\Part\\test\\stub\\ConstructorStubScriptImpl</class>
        </preScripts>
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        try{
            $factory->buildPreScriptChain($this->backFactory);
        } catch (ForceExitException $e){
            $this->assertInstanceOf('ChristianBudde\Part\BackendSingletonContainer', $e->data[0]);
        }

    }
    public function testBuildPostScriptWillGiveRightArgumentToConstructor()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <postScripts>
        <class>ChristianBudde\\Part\\test\\stub\\ConstructorStubScriptImpl</class>
        </postScripts>
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        try{
            $factory->buildPostScriptChain($this->backFactory);
        } catch (ForceExitException $e){
            $this->assertInstanceOf('ChristianBudde\Part\BackendSingletonContainer', $e->data[0]);
        }

    }

    public function testBuildPostScriptWillThrowExceptionIfFileNotFound()
    {

        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <postScripts>
        <class link='stubs/ThisFileIsNotFound.php'>ScriptExceptionStubImpl</class>
        </postScripts>
        </config>");

        $this->setExpectedException('ChristianBudde\Part\exception\FileNotFoundException');

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        $factory->buildPostScriptChain($this->backFactory);

    }

    public function testBuildPostScriptWillThrowExceptionIfScriptClassIsNotDefined()
    {
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        <postScripts>
        <class link='stub/ExceptionStubScriptImpl.php'>WrongClassName</class>
        </postScripts>
        </config>");

        $this->setExpectedException('ChristianBudde\Part\exception\ClassNotDefinedException');

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        $factory->buildPostScriptChain($this->backFactory);

    }

    public function testBuildConfigWillReturnInstanceOfConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        $retConfig = $factory->buildConfig();
        $this->assertInstanceOf('ChristianBudde\Part\Config', $retConfig, 'Did not return Config');
    }

    public function testBuildConfigWillReturnNewInstanceOfConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        $retConfig = $factory->buildConfig();
        $secRetConfig = $factory->buildConfig();
        $this->assertFalse(($config === $retConfig), 'Did not return a new instance of Config');
        $this->assertFalse(($retConfig === $secRetConfig), 'Did not return a new instance of Config');
    }

    public function testBuildBackendSingletonContainerReturnNewInstanceOfThat()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>{$this->defaultOwner}
        </config>");

        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $factory = new SiteFactoryImpl($config);
        $ret = $factory->buildBackendSingletonContainer($config);
        $ret2 = $factory->buildBackendSingletonContainer($config);
        $this->assertFalse(($ret === $ret2), 'Did not return a new instance of container');
    }
}
