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
    /** @var  DumpFile */
    private $dumpFile;

    public function setUp(){
        $this->logFile= new LogFileImpl(dirname(__FILE__)."/stubs/logFile");
        $this->logFile->delete();

    }
   /* THIS TEST ASSUMES THAT THE LogFileImpl EXTENDS FileImpl. */

    public function testLogWillLogToFile(){
        $msg = "SOME LOG MSG";
        $this->assertEquals("", $this->logFile->getContents());
        $this->logFile->log($msg, LogFile::LOG_LEVEL_ERROR);
        $this->assertContains($msg, $this->logFile->getContents());
    }

    public function testListLogListsLog(){
        $msg1 = uniqid("msg");
        $msg2 = uniqid("msg");

        $this->logFile->log($msg1, LogFile::LOG_LEVEL_ERROR);
        $this->logFile->log($msg2, LogFile::LOG_LEVEL_WARNING);

        $ar = $this->logFile->listLog();

        $this->assertTrue(is_array($ar));
        $this->assertEquals(2, count($ar));
        $this->assertArrayHasKey(0, $ar);
        $this->assertArrayHasKey(1, $ar);

        $ar1 = $ar[0];
        $ar2 = $ar[1];

        $this->assertArrayHasKey("message",$ar1);
        $this->assertArrayHasKey("message",$ar2);

        $this->assertArrayHasKey("time",$ar2);
        $this->assertArrayHasKey("time",$ar2);

        $this->assertArrayHasKey("level",$ar2);
        $this->assertArrayHasKey("level",$ar2);

        $this->assertEquals($msg1, $ar1["message"]);
        $this->assertEquals($msg2, $ar2["message"]);

        $this->assertEquals(LogFile::LOG_LEVEL_WARNING, $ar2["level"]);
        $this->assertEquals(LogFile::LOG_LEVEL_ERROR, $ar1["level"]);

    }

    public function testNewLinesAreOk(){
        $msg = "some \n message";
        $this->logFile->log($msg, LogFile::LOG_LEVEL_WARNING);
        $ar = $this->logFile->listLog();
        $this->assertEquals(1, count($ar));
        $this->assertEquals($msg, $ar[0]["message"]);
    }

    public function testLevelsAreRespected(){
        $msg1 = uniqid("msg");
        $msg2 = uniqid("msg");

        $this->logFile->log($msg1, LogFile::LOG_LEVEL_ERROR);
        $this->logFile->log($msg2, LogFile::LOG_LEVEL_WARNING);

        $ar1 = $this->logFile->listLog(LogFile::LOG_LEVEL_ERROR);
        $ar2 = $this->logFile->listLog(LogFile::LOG_LEVEL_WARNING);
        $ar3 = $this->logFile->listLog(LogFile::LOG_LEVEL_DEBUG);
        $ar4 = $this->logFile->listLog(LogFile::LOG_LEVEL_ERROR | LogFile::LOG_LEVEL_WARNING | LogFile::LOG_LEVEL_DEBUG);

        $this->assertEquals(1, count($ar1));
        $this->assertEquals(1, count($ar2));
        $this->assertEquals(0, count($ar3));
        $this->assertEquals(2, count($ar4));

        $this->assertEquals($msg1, $ar1[0]["message"]);
        $this->assertEquals($msg2, $ar2[0]["message"]);
        $this->assertEquals($msg1, $ar4[0]["message"]);
        $this->assertEquals($msg2, $ar4[1]["message"]);

    }

    public function testLogWillReturnNull(){
        $this->assertNull($this->logFile->log("SOME MSG", LogFile::LOG_LEVEL_WARNING));
    }

    public function testLogWillReturnDumpFile(){
        $this->dumpFile = $this->logFile->log("MSG", LogFile::LOG_LEVEL_WARNING, true);
        $this->assertInstanceOf("DumpFile", $this->dumpFile);
        $this->assertTrue($this->dumpFile->exists());
        $this->assertGreaterThan(0, strlen($this->dumpFile->getContents()));
    }

    public function testListLogWillContainDumpFile(){
        $d = $this->logFile->log("Some msg", LogFile::LOG_LEVEL_WARNING, true);
        $ar = $this->logFile->listLog();
        $ar1 = $ar[0];
        $this->assertArrayHasKey("dumpfile", $ar1);
        $this->assertEquals($ar1["dumpfile"], $d->getFilename());
        $this->dumpFile = $d;
    }

    public function tearDown(){
        $this->logFile->delete();
        if($this->dumpFile != null){
            $this->dumpFile->delete();
        }
    }




} 