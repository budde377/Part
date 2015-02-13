<?php
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\util\file\JSFileImpl;
use ChristianBudde\cbweb\util\file\JSRegisterImpl;
use PHPUnit_Framework_TestCase;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 5:44 PM
 * To change this template use File | Settings | File Templates.
 */
class JSRegisterImplTest extends PHPUnit_Framework_TestCase
{


    public function testGetRegisteredWillReturnEmptyArrayOnNoRegistered()
    {
        $jsRegister = new JSRegisterImpl();

        $registeredFiles = $jsRegister->getRegisteredFiles();

        $this->assertTrue(is_array($registeredFiles), 'Did not return array');
        $this->assertTrue(empty($registeredFiles), 'Array was not empty');
    }

    public function testRegisteredJSFilesWillBeRegistered()
    {
        $jsRegister = new JSRegisterImpl();
        $jsFile = new JSFileImpl(dirname(__FILE__) . '/../stubs/jsStub.js');

        $jsRegister->registerJSFile($jsFile);
        $registeredFiles = $jsRegister->getRegisteredFiles();

        $this->assertTrue(is_array($registeredFiles), 'Did not return array');
        $this->assertArrayHasKey(0, $registeredFiles, 'Did not contain index 0');
        $this->assertEquals($jsFile, $registeredFiles[0], 'The files did not match');

    }

    public function testRegisteredJSDuplicatesWillNotOccur()
    {
        $jsRegister = new JSRegisterImpl();
        $jsFile = new JSFileImpl(dirname(__FILE__) . '/../stubs/jsStub.js');
        $jsFile2 = new JSFileImpl(dirname(__FILE__) . '/../stubs/jsStub.js');

        $jsRegister->registerJSFile($jsFile);
        $jsRegister->registerJSFile($jsFile2);
        $registeredFiles = $jsRegister->getRegisteredFiles();
        $this->assertEquals(1, count($registeredFiles));
        $this->assertTrue($registeredFiles[0] === $jsFile, 'Did not keep the old file');
    }

}
