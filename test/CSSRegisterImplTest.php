<?php
use ChristianBudde\cbweb\CSSRegisterImpl;
use ChristianBudde\cbweb\CSSFileImpl;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 5:44 PM
 * To change this template use File | Settings | File Templates.
 */
class CSSRegisterImplTest extends PHPUnit_Framework_TestCase
{


    public function testGetRegisteredWillReturnEmptyArrayOnNoRegistered()
    {
        $cssRegister = new CSSRegisterImpl();

        $registeredFiles = $cssRegister->getRegisteredFiles();

        $this->assertTrue(is_array($registeredFiles), 'Did not return array');
        $this->assertTrue(empty($registeredFiles), 'Array was not empty');
    }

    public function testRegisteredCSSFilesWillBeRegistered()
    {
        $cssRegister = new CSSRegisterImpl();
        $cssFile = new CSSFileImpl(dirname(__FILE__) . '/stubs/cssStub.css');

        $cssRegister->registerCSSFile($cssFile);
        $registeredFiles = $cssRegister->getRegisteredFiles();

        $this->assertTrue(is_array($registeredFiles), 'Did not return array');
        $this->assertArrayHasKey(0, $registeredFiles, 'Did not contain index 0');
        $this->assertEquals($cssFile, $registeredFiles[0], 'The files did not match');

    }

    public function testRegisteredCSSDuplicatesWillNotOccur()
    {
        $cssRegister = new CSSRegisterImpl();
        $cssFile = new CSSFileImpl(dirname(__FILE__) . '/stubs/cssStub.css');
        $cssFile2 = new CSSFileImpl(dirname(__FILE__) . '/stubs/cssStub.css');

        $cssRegister->registerCSSFile($cssFile);
        $cssRegister->registerCSSFile($cssFile2);
        $registeredFiles = $cssRegister->getRegisteredFiles();
        $this->assertEquals(1, count($registeredFiles));
        $this->assertTrue($registeredFiles[0] === $cssFile, 'Did not keep the old file');
    }


}
