<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/29/12
 * Time: 11:13 AM
 * To change this template use File | Settings | File Templates.
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\BackendSingletonContainer;
use ChristianBudde\cbweb\ConfigImpl;
use ChristianBudde\cbweb\view\page_element\PageElementFactoryImpl;
use ChristianBudde\cbweb\exception\ClassNotInstanceOfException;
use Exception;
use ChristianBudde\cbweb\test\stub\NullBackendSingletonContainerImpl;
use PHPUnit_Framework_TestCase;
use SimpleXMLElement;

class PageElementFactoryTest extends PHPUnit_Framework_TestCase
{
    private $defaultOwner = "<siteInfo><domain name='test' extension='dk'/><owner name='Admin Jensen' mail='test@test.dk' username='asd' /></siteInfo>";

    /** @var $backFactory BackendSingletonContainer */
    private $backFactory;

    protected function setUp()
    {
        $this->backFactory = new NullBackendSingletonContainerImpl();
    }

    public function testWillReturnNullIfPageElementIsNil()
    {
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $element = $pageElementFactory->getPageElement('NilElement');
        $this->assertNull($element, 'Did not return null on element not in list');
    }

    public function testWillReturnInstanceIfPageElementIsNotInConfigButIsImported()
    {
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $element = $pageElementFactory->getPageElement('ChristianBudde\cbweb\test\stub\NullPageElementImpl');
        $this->assertInstanceOf('ChristianBudde\cbweb\test\stub\NullPageElementImpl', $element);
    }

    public function testWillReturnPageElementIfElementInList()
    {
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/NullPageElementImpl.php'>ChristianBudde\\cbweb\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $element = $pageElementFactory->getPageElement('someElement');
        $this->assertTrue(is_object($element), 'Did not return an object');
        $this->assertInstanceOf('ChristianBudde\cbweb\test\stub\NullPageElementImpl', $element, 'Did not return element of right instance.');

    }

    public function testClassPathIsOptional()
    {
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' >ChristianBudde\\cbweb\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $element = $pageElementFactory->getPageElement('someElement');
        $this->assertTrue(is_object($element), 'Did not return an object');
        $this->assertInstanceOf('ChristianBudde\cbweb\test\stub\NullPageElementImpl', $element, 'Did not return element of right instance.');

    }

    public function testPageElementWillBeCached()
    {
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/NullPageElementImpl.php'>ChristianBudde\\cbweb\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $element = $pageElementFactory->getPageElement('someElement');
        $element2 = $pageElementFactory->getPageElement('someElement');
        $this->assertTrue($element === $element2);

    }

    public function testPageElementWillBeCachedAlsoWhenNotInConfig()
    {
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $element = $pageElementFactory->getPageElement('NullPageElementImpl');
        $element2 = $pageElementFactory->getPageElement('NullPageElementImpl');
        $this->assertTrue($element === $element2);

    }

    public function testPageElementClearCacheWillClearCache()
    {
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/NullPageElementImpl.php'>ChristianBudde\\cbweb\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $element = $pageElementFactory->getPageElement('someElement');
        $pageElementFactory->clearCache();
        $element2 = $pageElementFactory->getPageElement('someElement');
        $this->assertFalse($element === $element2);

    }

    public function testPageElementCacheCanBeDisabled()
    {
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/NullPageElementImpl.php'>ChristianBudde\\cbweb\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $element = $pageElementFactory->getPageElement('someElement');
        $element2 = $pageElementFactory->getPageElement('someElement', false);
        $this->assertFalse($element === $element2);
    }

    public function testPageElementCacheCanBeDisabledAlsoWhenElementNotInConfig()
    {
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $element = $pageElementFactory->getPageElement('ChristianBudde\\cbweb\\test\\stub\\NullPageElementImpl');
        $element2 = $pageElementFactory->getPageElement('ChristianBudde\\cbweb\\test\\stub\\NullPageElementImpl', false);
        $this->assertFalse($element === $element2);
    }

    public function testPageElementCacheWillBeUpdated()
    {
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/NullPageElementImpl.php'>ChristianBudde\\cbweb\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $element = $pageElementFactory->getPageElement('someElement');
        $element2 = $pageElementFactory->getPageElement('someElement', false);
        $element3 = $pageElementFactory->getPageElement('someElement');
        $this->assertFalse($element === $element2);
        $this->assertTrue($element2 === $element3);
    }

    public function testWillReturnThrowExceptionIfElementNotInstanceOfPageElement()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/StubScriptImpl.php'>ChristianBudde\\cbweb\\test\\stub\\StubScriptImpl</class>
        </pageElements>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $exceptionWasThrown = false;
        try {
            $pageElementFactory->getPageElement('someElement');
        } catch (Exception $exception) {
            /** @var $exception \ChristianBudde\cbweb\exception\ClassNotInstanceOfException */
            $this->assertInstanceOf('ChristianBudde\cbweb\exception\ClassNotInstanceOfException', $exception);
            $exceptionWasThrown = true;
            $this->assertEquals('ChristianBudde\\cbweb\\test\\stub\\StubScriptImpl', $exception->getClass(), 'Was not expected class');
            $this->assertEquals('PageElement', $exception->getExpectedInstance(), 'Was not expected instance');

        }

        $this->assertTrue($exceptionWasThrown, 'No exception was thrown');


    }

    public function testWillReturnThrowExceptionIfElementNotInstanceOfPageElementAndNotInConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $exceptionWasThrown = false;
        try {
            $pageElementFactory->getPageElement('ChristianBudde\cbweb\test\stub\StubScriptImpl');
        } catch (ClassNotInstanceOfException $exception) {
            /** @var $exception ClassNotInstanceOfException */
            $this->assertInstanceOf('ChristianBudde\cbweb\exception\ClassNotInstanceOfException', $exception);
            $exceptionWasThrown = true;
            $this->assertEquals('ChristianBudde\cbweb\test\stub\StubScriptImpl', $exception->getClass(), 'Was not expected class');
            $this->assertEquals('PageElement', $exception->getExpectedInstance(), 'Was not expected instance');

        }

        $this->assertTrue($exceptionWasThrown, 'No exception was thrown');


    }


    public function testWillThrowExceptionIfInvalidLink()
    {
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='notAValidLink'>ChristianBudde\\cbweb\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $this->setExpectedException('ChristianBudde\cbweb\exception\FileNotFoundException');
        $pageElementFactory->getPageElement('someElement');

    }

    public function testWillThrowExceptionIfClassNotDefined()
    {
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/NullPageElementImpl.php'>NotDefinedClass</class>
        </pageElements>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $this->setExpectedException('ChristianBudde\cbweb\exception\ClassNotDefinedException');
        $pageElementFactory->getPageElement('someElement');

    }


}
