<?php


/**
 * User: budde
 * Date: 5/28/12
 * Time: 2:53 PM
 */
namespace ChristianBudde\Part;


use ChristianBudde\Part\exception\ClassNotInstanceOfException;

use ChristianBudde\Part\exception\ForceExitException;
use ChristianBudde\Part\exception\ScriptHasRunException;
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

    private function setupFactory($config = null){

        if($config == null){
            $config = "<config>{$this->defaultOwner}</config>";
        }
        $configXML = simplexml_load_string($config);
        $this->config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->factory = new SiteFactoryImpl($this->config);

    }


    public function testBuildPreAndPostScriptWillReturnScriptChainOnEmptyConfig()
    {
        $this->setupFactory();

        $this->assertInstanceOf('ChristianBudde\Part\util\script\ScriptChain', $this->factory->buildPostScriptChain($this->backFactory), 'The buildPostScriptChain return must be instance of ScriptChain');
        $this->assertInstanceOf('ChristianBudde\Part\util\script\ScriptChain', $this->factory->buildPreScriptChain($this->backFactory), 'The buildPreScriptChain return must be instance of ScriptChain');

    }

    public function testBuildPreScriptWillReturnScriptChainWithScriptsSpecifiedInConfig()
    {

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <preScripts>
        <class link='util/script/ExceptionStubScriptImpl.php'>ChristianBudde\Part\util\script\ExceptionStubScriptImpl</class>
        </preScripts>
        </config>");
        $preScripts = $this->factory->buildPreScriptChain($this->backFactory);

        $exceptionWasThrown = false;
        try {
            $preScripts->run('PreScript', null);

        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\exception\ScriptHasRunException', $exception, 'The wrong exception was thrown.');
            /** @var $exception ScriptHasRunException */
            $exceptionWasThrown = true;
            $this->assertEquals('PreScript', $exception->getName(), 'The Script ran with wrong name.');
            $this->assertNull($exception->getArgs(), 'Script ran with wrong argument');
        }
        $this->assertTrue($exceptionWasThrown, 'A exception was not thrown.');

    }

    public function testBuildPreScriptWillReturnScriptChainWithScriptsSpecifiedInConfigButNoLink()
    {

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <preScripts>
        <class>ChristianBudde\\Part\\util\\script\\ExceptionStubScriptImpl</class>
        </preScripts>
        </config>");
        $preScripts = $this->factory->buildPreScriptChain($this->backFactory);

        $exceptionWasThrown = false;
        try {
            $preScripts->run('PreScript', null);

        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\exception\ScriptHasRunException', $exception, 'The wrong exception was thrown.');
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
        $this->setupFactory("
        <config>{$this->defaultOwner}
        <preScripts>
        <class link='view/page_element/NullPageElementImpl.php'>ChristianBudde\\Part\\view\\page_element\\NullPageElementImpl</class>
        </preScripts>
        </config>");

        $exceptionWasThrown = false;
        try {
            $this->factory->buildPreScriptChain($this->backFactory);
        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\exception\ClassNotInstanceOfException', $exception, 'The wrong exception was thrown.');
            /** @var $exception \ChristianBudde\Part\exception\ClassNotInstanceOfException */
            $exceptionWasThrown = true;
            $this->assertEquals('ChristianBudde\Part\view\page_element\NullPageElementImpl', $exception->getClass(), 'Was not expected class');
            $this->assertEquals('Script', $exception->getExpectedInstance(), 'Was not expected instance');
        }
        $this->assertTrue($exceptionWasThrown, 'A exception was not thrown.');

    }


    public function testBuildPostScriptWillThrowExceptionWithNotScriptInConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $this->setupFactory("
        <config>{$this->defaultOwner}
        <postScripts>
        <class link='view/page_element/NullPageElementImpl.php'>ChristianBudde\\Part\\view\\page_element\\NullPageElementImpl</class>
        </postScripts>
        </config>");

        $exceptionWasThrown = false;
        try {
            $this->factory->buildPostScriptChain($this->backFactory);
        } catch (Exception $exception) {
            $this->assertInstanceOf('ChristianBudde\Part\exception\ClassNotInstanceOfException', $exception, 'The wrong exception was thrown.');
            /** @var $exception ClassNotInstanceOfException */
            $exceptionWasThrown = true;
            $this->assertEquals('ChristianBudde\Part\view\page_element\NullPageElementImpl', $exception->getClass(), 'Was not expected class');
            $this->assertEquals('Script', $exception->getExpectedInstance(), 'Was not expected instance');
        }
        $this->assertTrue($exceptionWasThrown, 'A exception was not thrown.');

    }

    public function testBuildPostScriptWillReturnScriptChainWithScriptsSpecifiedInConfig()
    {

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <postScripts>
        <class link='util/script/ExceptionStubScriptImpl.php'>ChristianBudde\\Part\\util\\script\\ExceptionStubScriptImpl</class>
        </postScripts>
        </config>");
        $postScripts = $this->factory->buildPostScriptChain($this->backFactory);

        $this->setExpectedException('ChristianBudde\\Part\\exception\\ScriptHasRunException');
        $postScripts->run('PostScript', null);

    }

    public function testBuildPostScriptWillReturnScriptChainWithScriptsSpecifiedInConfigButNoLink()
    {

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <postScripts>
        <class>ChristianBudde\\Part\\util\\script\\ExceptionStubScriptImpl</class>
        </postScripts>
        </config>");
        $postScripts = $this->factory->buildPostScriptChain($this->backFactory);

        $this->setExpectedException('ChristianBudde\\Part\\exception\\ScriptHasRunException');
        $postScripts->run('PostScript', null);

    }

    public function testBuildPreScriptWillThrowExceptionIfFileNotFound()
    {
        $this->setExpectedException('ChristianBudde\Part\exception\FileNotFoundException');

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <preScripts>
        <class link='stubs/ThisFileIsNotFound.php'>ChristianBudde\\Part\\test\\stub\\ScriptExceptionStubImpl</class>
        </preScripts>
        </config>");
        $this->factory->buildPreScriptChain($this->backFactory);

    }

    public function testBuildPreScriptWillThrowExceptionIfScriptClassIsNotDefined()
    {
        $this->setExpectedException('ChristianBudde\Part\exception\ClassNotDefinedException');

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <preScripts>
        <class link='util/script/ExceptionStubScriptImpl.php'>WrongClassName</class>
        </preScripts>
        </config>");
        $this->factory->buildPreScriptChain($this->backFactory);

    }

    public function testBuildPreScriptWillGiveRightArgumentToConstructor()
    {
        $this->setupFactory("
        <config>{$this->defaultOwner}
        <preScripts>
        <class>ChristianBudde\\Part\\util\\script\\ConstructorStubScriptImpl</class>
        </preScripts>
        </config>");
        try{
            $this->factory->buildPreScriptChain($this->backFactory);
        } catch (ForceExitException $e){
            $this->assertTrue($e->data[0] === $this->backFactory);
        }

    }
    public function testBuildPostScriptWillGiveRightArgumentToConstructor()
    {
        $this->setupFactory("
        <config>{$this->defaultOwner}
        <postScripts>
        <class>ChristianBudde\\Part\\util\\script\\ConstructorStubScriptImpl</class>
        </postScripts>
        </config>");
        try{
            $this->factory->buildPostScriptChain($this->backFactory);
        } catch (ForceExitException $e){
            $this->assertTrue($e->data[0] === $this->backFactory);
        }

    }

    public function testBuildPostScriptWillThrowExceptionIfFileNotFound()
    {

        $this->setExpectedException('ChristianBudde\Part\exception\FileNotFoundException');

        $this->setupFactory("
        <config>{$this->defaultOwner}
        <postScripts>
        <class link='stubs/ThisFileIsNotFound.php'>ScriptExceptionStubImpl</class>
        </postScripts>
        </config>");
        $this->factory->buildPostScriptChain($this->backFactory);

    }

    public function testBuildPostScriptWillThrowExceptionIfScriptClassIsNotDefined()
    {
        $this->setExpectedException('ChristianBudde\Part\exception\ClassNotDefinedException');
        $this->setupFactory("
        <config>{$this->defaultOwner}
        <postScripts>
        <class link='util/script/ExceptionStubScriptImpl.php'>WrongClassName</class>
        </postScripts>
        </config>");
        $this->factory->buildPostScriptChain($this->backFactory);

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
