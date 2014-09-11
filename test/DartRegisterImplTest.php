<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 11:46
 */
use ChristianBudde\cbweb\DartRegisterImpl;
use ChristianBudde\cbweb\DartFileImpl;

class DartRegisterImplTest extends PHPUnit_Framework_TestCase
{

    /** @var DartRegisterImpl */
    private $register;

    public function setUp(){
        $this->register = new DartRegisterImpl();
    }

    public function testGetRegisteredWillReturnEmptyArrayOnNoRegistered()
    {

        $registeredFiles = $this->register->getRegisteredFiles();

        $this->assertTrue(is_array($registeredFiles), 'Did not return array');
        $this->assertTrue(empty($registeredFiles), 'Array was not empty');
    }

    public function testRegisteredDartFilesWillBeRegistered()
    {
        $dartFile = new DartFileImpl(dirname(__FILE__) . '/stubs/dartStub.dart');

        $this->register->registerDartFile($dartFile);
        $registeredFiles = $this->register->getRegisteredFiles();

        $this->assertTrue(is_array($registeredFiles), 'Did not return array');
        $this->assertArrayHasKey(0, $registeredFiles, 'Did not contain index 0');
        $this->assertEquals($dartFile, $registeredFiles[0], 'The files did not match');

    }

    public function testRegisteredDartDuplicatesWillNotOccur()
    {
        $dartFile = new DartFileImpl(dirname(__FILE__) . '/stubs/dartStub.dart');
        $dartFile2 = new DartFileImpl(dirname(__FILE__) . '/stubs/dartStub.dart');


        $this->register->registerDartFile($dartFile);
        $this->register->registerDartFile($dartFile2);
        $registeredFiles = $this->register->getRegisteredFiles();
        $this->assertEquals(1, count($registeredFiles));
        $this->assertTrue($registeredFiles[0] === $dartFile, 'Did not keep the old file');
    }
}
