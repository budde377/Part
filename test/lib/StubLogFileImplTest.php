<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/17/14
 * Time: 4:52 PM
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\log\Logger;

use ChristianBudde\cbweb\util\file\StubLogFileImpl;
use PHPUnit_Framework_TestCase;

class StubLogFileImplTest extends PHPUnit_Framework_TestCase
{
    /** @var  StubLogFileImpl */
    private $logFile;

    protected function setUp()
    {
        $this->logFile = new StubLogFileImpl();
    }


    public function testLogFileDoesNotExist()
    {
        $this->assertFalse($this->logFile->exists());
    }

    public function testLogFileDoesNotWrite()
    {
        $this->logFile->log("SomeMsg", Logger::LOG_LEVEL_ERROR);
        $this->assertFalse($this->logFile->exists());
    }

    public function testLogFileDoesNotList()
    {
        $this->logFile->log("LOL", Logger::LOG_LEVEL_ERROR);
        $this->assertTrue(is_array($l = $this->logFile->listLog()));
        $this->assertEquals(0, count($l));
    }

    public function testClearDoesNothing()
    {
        $this->logFile->clearLog();
        $this->assertFalse($this->logFile->exists());

    }

    public function testLogReturnsNull()
    {
        $this->assertNull($this->logFile->log("", 1));
    }

    public function testLogDoesReturnFile()
    {
        $this->assertInstanceOf("ChristianBudde\\cbweb\\util\\file\\StubDumpFileImpl", $this->logFile->log("MSG", Logger::LOG_LEVEL_ERROR, true));
    }

}