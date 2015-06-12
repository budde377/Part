<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/29/12
 * Time: 11:13 AM
 * To change this template use File | Settings | File Templates.
 */
namespace ChristianBudde\Part\test;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\ConfigImpl;
use ChristianBudde\Part\exception\ClassNotInstanceOfException;
use ChristianBudde\Part\test\stub\NullBackendSingletonContainerImpl;
use ChristianBudde\Part\test\stub\StubBackendSingletonContainerImpl;
use ChristianBudde\Part\view\page_element\PageElementFactoryImpl;
use Exception;
use PHPUnit_Framework_TestCase;
use SimpleXMLElement;

class PageElementFactoryTest extends PHPUnit_Framework_TestCase
{
    private $defaultOwner = "<siteInfo><domain name='test' extension='dk'/><owner name='Admin Jensen' mail='test@test.dk' username='asd' /></siteInfo>";

    /** @var $backFactory BackendSingletonContainer */
    private $backFactory;

    /** @var  PageElementFactoryImpl */
    private $pageElementFactory;

    protected function setUp()
    {
        $this->setUpConfig("<config>{$this->defaultOwner}</config>");
    }

    private function setUpConfig($string){
        $configXML = simplexml_load_string($string);
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $this->backFactory = new StubBackendSingletonContainerImpl();
        $this->backFactory->setConfigInstance($config);
        $this->pageElementFactory = new PageElementFactoryImpl($this->backFactory);
    }

    public function testWillReturnNullIfPageElementIsNil()
    {

        $element = $this->pageElementFactory->getPageElement('NilElement');
        $this->assertNull($element, 'Did not return null on element not in list');
    }

    public function testWillReturnInstanceIfPageElementIsNotInConfigButIsImported()
    {
        $element = $this->pageElementFactory->getPageElement('ChristianBudde\Part\test\stub\NullPageElementImpl');
        $this->assertInstanceOf('ChristianBudde\Part\test\stub\NullPageElementImpl', $element);
    }

    public function testWillReturnPageElementIfElementInList()
    {
        $this->setupConfig("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/NullPageElementImpl.php'>ChristianBudde\\Part\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");

        $element = $this->pageElementFactory->getPageElement('someElement');
        $this->assertTrue(is_object($element), 'Did not return an object');
        $this->assertInstanceOf('ChristianBudde\Part\test\stub\NullPageElementImpl', $element, 'Did not return element of right instance.');

    }

    public function testClassPathIsOptional()
    {
        $this->setUpConfig("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' >ChristianBudde\\Part\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");
        $element = $this->pageElementFactory->getPageElement('someElement');
        $this->assertTrue(is_object($element), 'Did not return an object');
        $this->assertInstanceOf('ChristianBudde\Part\test\stub\NullPageElementImpl', $element, 'Did not return element of right instance.');

    }

    public function testPageElementWillBeCached()
    {
        $this->setupConfig("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/NullPageElementImpl.php'>ChristianBudde\\Part\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");

        $element = $this->pageElementFactory->getPageElement('someElement');
        $element2 = $this->pageElementFactory->getPageElement('someElement');
        $this->assertTrue($element === $element2);

    }

    public function testPageElementWillBeCachedAlsoWhenNotInConfig()
    {
        $this->setupConfig("
        <config>
        {$this->defaultOwner}
        </config>");

        $element = $this->pageElementFactory->getPageElement('NullPageElementImpl');
        $element2 = $this->pageElementFactory->getPageElement('NullPageElementImpl');
        $this->assertTrue($element === $element2);

    }

    public function testPageElementClearCacheWillClearCache()
    {
        $this->setupConfig("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/NullPageElementImpl.php'>ChristianBudde\\Part\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");

        $element = $this->pageElementFactory->getPageElement('someElement');
        $this->pageElementFactory->clearCache();
        $element2 = $this->pageElementFactory->getPageElement('someElement');
        $this->assertFalse($element === $element2);

    }

    public function testPageElementCacheCanBeDisabled()
    {
        $this->setupConfig("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/NullPageElementImpl.php'>ChristianBudde\\Part\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");

        $element = $this->pageElementFactory->getPageElement('someElement');
        $element2 = $this->pageElementFactory->getPageElement('someElement', false);
        $this->assertFalse($element === $element2);
    }

    public function testPageElementCacheCanBeDisabledAlsoWhenElementNotInConfig()
    {
        $this->setupConfig("
        <config>
        {$this->defaultOwner}
        </config>");

        $element = $this->pageElementFactory->getPageElement('ChristianBudde\\Part\\test\\stub\\NullPageElementImpl');
        $element2 = $this->pageElementFactory->getPageElement('ChristianBudde\\Part\\test\\stub\\NullPageElementImpl', false);
        $this->assertFalse($element === $element2);
    }

    public function testPageElementCacheWillBeUpdated()
    {
        $this->setupConfig("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/NullPageElementImpl.php'>ChristianBudde\\Part\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");

        $element = $this->pageElementFactory->getPageElement('someElement');
        $element2 = $this->pageElementFactory->getPageElement('someElement', false);
        $element3 = $this->pageElementFactory->getPageElement('someElement');
        $this->assertFalse($element === $element2);
        $this->assertTrue($element2 === $element3);
    }

    public function testWillReturnThrowExceptionIfElementNotInstanceOfPageElement()
    {
        /** @var $configXML SimpleXMLElement */
        $this->setupConfig("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/StubScriptImpl.php'>ChristianBudde\\Part\\test\\stub\\StubScriptImpl</class>
        </pageElements>
        </config>");

        $exceptionWasThrown = false;
        try {
            $this->pageElementFactory->getPageElement('someElement');
        } catch (Exception $exception) {
            /** @var $exception \ChristianBudde\Part\exception\ClassNotInstanceOfException */
            $this->assertInstanceOf('ChristianBudde\Part\exception\ClassNotInstanceOfException', $exception);
            $exceptionWasThrown = true;
            $this->assertEquals('ChristianBudde\\Part\\test\\stub\\StubScriptImpl', $exception->getClass(), 'Was not expected class');
            $this->assertEquals('PageElement', $exception->getExpectedInstance(), 'Was not expected instance');

        }

        $this->assertTrue($exceptionWasThrown, 'No exception was thrown');


    }

    public function testWillReturnThrowExceptionIfElementNotInstanceOfPageElementAndNotInConfig()
    {
        /** @var $configXML SimpleXMLElement */
        $this->setupConfig("
        <config>
        {$this->defaultOwner}
        </config>");

        $exceptionWasThrown = false;
        try {
            $this->pageElementFactory->getPageElement('ChristianBudde\Part\test\stub\StubScriptImpl');
        } catch (ClassNotInstanceOfException $exception) {
            /** @var $exception ClassNotInstanceOfException */
            $this->assertInstanceOf('ChristianBudde\Part\exception\ClassNotInstanceOfException', $exception);
            $exceptionWasThrown = true;
            $this->assertEquals('ChristianBudde\Part\test\stub\StubScriptImpl', $exception->getClass(), 'Was not expected class');
            $this->assertEquals('PageElement', $exception->getExpectedInstance(), 'Was not expected instance');

        }

        $this->assertTrue($exceptionWasThrown, 'No exception was thrown');


    }


    public function testWillThrowExceptionIfInvalidLink()
    {
        $this->setupConfig("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='notAValidLink'>ChristianBudde\\Part\\test\\stub\\NullPageElementImpl</class>
        </pageElements>
        </config>");
        $this->setExpectedException('ChristianBudde\Part\exception\FileNotFoundException');
        $this->pageElementFactory->getPageElement('someElement');

    }

    public function testWillThrowExceptionIfClassNotDefined()
    {
        $this->setupConfig("
        <config>
        {$this->defaultOwner}
        <pageElements>
            <class name='someElement' link='stub/NullPageElementImpl.php'>NotDefinedClass</class>
        </pageElements>
        </config>");
        $this->setExpectedException('ChristianBudde\Part\exception\ClassNotDefinedException');
        $this->pageElementFactory->getPageElement('someElement');

    }


}
