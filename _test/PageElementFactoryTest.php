<?php
require_once dirname(__FILE__) . '/../_class/PageElementFactoryImpl.php';
require_once dirname(__FILE__) . '/../_class/ConfigImpl.php';
require_once dirname(__FILE__) . '/_stub/NullBackendSingletonContainerImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/29/12
 * Time: 11:13 AM
 * To change this template use File | Settings | File Templates.
 */
class PageElementFactoryTest extends PHPUnit_Framework_TestCase
{

    /** @var $backFactory BackendSingletonContainer */
    private $backFactory;

    protected function setUp()
    {
        $this->backFactory = new NullBackendSingletonContainerImpl();
    }

    public function testWillReturnNullIfPageElementIsNil()
    {
        $configXML = simplexml_load_string('<config></config>');
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $element = $pageElementFactory->getPageElement('NilElement');
        $this->assertNull($element, 'Did not return null on element not in list');
    }

    public function testWillReturnPageElementIfElementInList()
    {
        $configXML = simplexml_load_string('
        <config>
        <pageElements>
            <class name="someElement" link="_stub/NullPageElementImpl.php">NullPageElementImpl</class>
        </pageElements>
        </config>');
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $element = $pageElementFactory->getPageElement('someElement');
        $this->assertTrue(is_object($element), 'Did not return an object');
        $this->assertInstanceOf('NullPageElementImpl', $element, 'Did not return element of right instance.');

    }

    public function testWillReturnThrowExceptionIfElementNotInstanceOfPageElement()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string('
        <config>
        <pageElements>
            <class name="someElement" link="_stub/StubScriptImpl.php">StubScriptImpl</class>
        </pageElements>
        </config>');
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $exceptionWasThrown = false;
        try {
            $element = $pageElementFactory->getPageElement('someElement');
        } catch (Exception $exception) {
            /** @var $exception ClassNotInstanceOfException */
            $this->assertInstanceOf('ClassNotInstanceOfException', $exception);
            $exceptionWasThrown = true;
            $this->assertEquals('StubScriptImpl', $exception->getClass(), 'Was not expected class');
            $this->assertEquals('PageElement', $exception->getExpectedInstance(), 'Was not expected instance');

        }

        $this->assertTrue($exceptionWasThrown, 'No exception was thrown');


    }

    public function testWillThrowExceptionIfInvalidLink()
    {
        $configXML = simplexml_load_string('
        <config>
        <pageElements>
            <class name="someElement" link="notAValidLink">PageElementNullImpl</class>
        </pageElements>
        </config>');
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $this->setExpectedException('FileNotFoundException');
        $pageElementFactory->getPageElement('someElement');

    }

    public function testWillThrowExceptionIfClassNotDefined()
    {
        $configXML = simplexml_load_string('
        <config>
        <pageElements>
            <class name="someElement" link="_stub/NullPageElementImpl.php">NotAValidClassName</class>
        </pageElements>
        </config>');
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $pageElementFactory = new PageElementFactoryImpl($config, $this->backFactory);
        $this->setExpectedException('ClassNotDefinedException');
        $pageElementFactory->getPageElement('someElement');

    }
}
