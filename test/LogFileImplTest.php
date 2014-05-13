<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 10:32 AM
 */

class LogFileImplTest extends PHPUnit_Framework_TestCase{
    /** @var  LogFileImpl */
    private $logFile;

    public function setUp(){
        $this->logFile= new LogFileImpl(dirname(__FILE__)."/stubs/".uniqid("file"));


    }
   /* THIS TEST ASSUMES THAT THE LogFileImpl EXTENDS FileImpl. */

    public function testLogWillLogToFile(){
        $msg = "SOME LOG MSG";
        $this->assertEquals("", $this->logFile->getContents());
        $this->logFile->log($msg, LogFile::LOG_LEVEL_ERROR);
        $this->assertContains($msg, $this->logFile->getContents());
    }

    public function tearDown(){
        $this->logFile->delete();
    }
} 