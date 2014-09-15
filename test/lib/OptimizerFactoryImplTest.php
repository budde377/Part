<?php
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\ConfigImpl;
use ChristianBudde\cbweb\util\file\OptimizerFactoryImpl;
use ChristianBudde\cbweb\exception\ClassNotInstanceOfException;
use Exception;
use PHPUnit_Framework_TestCase;
use SimpleXMLElement;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/11/12
 * Time: 9:41 AM
 * To change this template use File | Settings | File Templates.
 */
class OptimizerFactoryImplTest extends PHPUnit_Framework_TestCase
{
    private $defaultOwner = "<siteInfo><domain name='test' extension='dk'/><owner name='Admin Jensen' mail='test@test.dk' username='asd' /></siteInfo>";

    public function testWillReturnNullIfOptimizerIsNil()
    {
        $configXML = simplexml_load_string("<config>{$this->defaultOwner}</config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $optimizerFactory = new OptimizerFactoryImpl($config);
        $element = $optimizerFactory->getOptimizer('NilElement');
        $this->assertNull($element, 'Did not return null on element not in list');
    }

    public function testWillReturnOptimizerIfElementInList()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <optimizers>
            <class name='someElement' link='stub/NullOptimizerImpl.php'>ChristianBudde\\cbweb\\test\\stub\\NullOptimizerImpl</class>
        </optimizers>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $optimizerFactory = new OptimizerFactoryImpl($config);
        $element = $optimizerFactory->getOptimizer('someElement');
        $this->assertTrue(is_object($element), 'Did not return an object');
        $this->assertInstanceOf('ChristianBudde\cbweb\test\stub\NullOptimizerImpl', $element, 'Did not return element of right instance.');

    }

    public function testWillReturnOptimizerIfElementInListButNoLink()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <optimizers>
            <class name='someElement'>ChristianBudde\\cbweb\\test\\stub\\NullOptimizerImpl</class>
        </optimizers>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $optimizerFactory = new OptimizerFactoryImpl($config);
        $element = $optimizerFactory->getOptimizer('someElement');
        $this->assertTrue(is_object($element), 'Did not return an object');
        $this->assertInstanceOf('ChristianBudde\cbweb\test\stub\NullOptimizerImpl', $element, 'Did not return element of right instance.');

    }

    public function testWillReturnThrowExceptionIfElementNotInstanceOfOptimizer()
    {
        /** @var $configXML SimpleXMLElement */
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <optimizers>
            <class name='someElement' link='stub/StubScriptImpl.php'>ChristianBudde\\cbweb\\test\\stub\\StubScriptImpl</class>
        </optimizers>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $optimizerFactory = new OptimizerFactoryImpl($config);
        $exceptionWasThrown = false;
        try {
            $optimizerFactory->getOptimizer('someElement');
        } catch (Exception $exception) {
            /** @var $exception ClassNotInstanceOfException */
            $this->assertInstanceOf('ChristianBudde\cbweb\exception\ClassNotInstanceOfException', $exception);
            $exceptionWasThrown = true;
            $this->assertEquals('ChristianBudde\\cbweb\\test\\stub\\StubScriptImpl', $exception->getClass(), 'Was not expected class');
            $this->assertEquals('Optimizer', $exception->getExpectedInstance(), 'Was not expected instance');

        }

        $this->assertTrue($exceptionWasThrown, 'No exception was thrown');


    }

    public function testWillThrowExceptionIfInvalidLink()
    {
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <optimizers>
            <class name='someElement' link='notAValidLink'>ChristianBudde\\cbweb\\test\\stub\\NullOptimizerImpl</class>
        </optimizers>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $optimizerFactory = new OptimizerFactoryImpl($config);
        $this->setExpectedException('ChristianBudde\cbweb\exception\FileNotFoundException');
        $optimizerFactory->getOptimizer('someElement');

    }

    public function testWillThrowExceptionIfClassNotDefined()
    {
        $configXML = simplexml_load_string("
        <config>
        {$this->defaultOwner}
        <optimizers>
            <class name='someElement' link='stub/NullOptimizerImpl.php'>NotAValidClassName</class>
        </optimizers>
        </config>");
        $config = new ConfigImpl($configXML, dirname(__FILE__) . '/');
        $optimizerFactory = new OptimizerFactoryImpl($config);
        $this->setExpectedException('ChristianBudde\cbweb\exception\ClassNotDefinedException');
        $optimizerFactory->getOptimizer('someElement');

    }
}