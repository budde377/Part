<?php
use ChristianBudde\cbweb\LogFileImpl;
use ChristianBudde\cbweb\Folder;
use ChristianBudde\cbweb\LoggerImpl;
use ChristianBudde\cbweb\FolderImpl;
use ChristianBudde\cbweb\Logger;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/3/12
 * Time: 9:37 PM
 * To change this template use File | Settings | File Templates.
 */
class LoggerImplTest extends PHPUnit_Framework_TestCase
{
    /** @var  LogFileImpl */
    private $logFile;

    /** @var Folder */
    private $folder;
    /** @var  LoggerImpl */
    private $logger;


    public function setUp()
    {
        $this->folder = new FolderImpl('/tmp/testing' . time() . '/');
        $this->logFile = new LogFileImpl($this->folder->getAbsolutePath() . "/" . uniqid());
        $this->logger = new LoggerImpl($this->logFile->getAbsoluteFilePath());
    }

    public function testLoggerLogsToLogFile()
    {
        $this->logger->log($level = 1337, $m = "Some message");
        $l = $this->logFile->listLog();
        $this->assertArrayHasKey(0, $l);
        $this->assertEquals(1, count($l));
        $this->assertArrayHasKey('level', $l[0]);
        $this->assertArrayHasKey('message', $l[0]);
        $this->assertArrayHasKey('time', $l[0]);
        $this->assertEquals(3, count($l[0]));
        $this->assertEquals($l[0]['level'], $level);
        $this->assertEquals($l[0]['message'], $m);
    }

    public function testLoggerSavesDump()
    {
        $this->logger->log($level = 1337, $m = "Some message");
        $l = $this->logFile->listLog();
        $this->assertArrayHasKey(0, $l);
        $this->assertEquals(1, count($l));
        $this->assertArrayHasKey('level', $l[0]);
        $this->assertArrayHasKey('message', $l[0]);
        $this->assertArrayHasKey('time', $l[0]);
        $this->assertEquals(3, count($l[0]));
        $this->assertEquals($l[0]['level'], $level);
        $this->assertEquals($l[0]['message'], $m);
    }

    public function testContextWillBeSaved()
    {
        $this->logger->log($level = 132, $message = "Some Message", $context = [123, 452345]);
        $l = $this->logger->listLog($level);
        $this->assertArrayHasKey('time', $l[0]);
        unset($l[0]['time']);
        $this->assertEquals([['level' => $level, 'message' => $message, 'context' => $context]], $l);
    }

    public function testClearWillClear()
    {
        $this->logger->log(132, "Some Message", [123, 452345]);
        $this->logger->log(132, "Some Message", [123, 452345]);
        $this->logger->clearLog();
        $l = $this->logger->listLog(132);
        $this->assertEquals(0, count($l));

    }

    public function testContextWillNotBeListed()
    {
        $this->logger->log($level = 132, $message = "Some Message", $context = [123, 452345]);
        $l = $this->logger->listLog($level, false);
        $this->assertArrayHasKey('time', $l[0]);
        unset($l[0]['time']);
        $this->assertEquals([['level' => $level, 'message' => $message]], $l);
    }

    public function testListWillLimitResultWithRespectToTime()
    {
        $this->logger->log($level = 132, "Some Message", [123, 452345]);
        sleep(2);
        $t = time();
        $this->logger->log($level, $message = "Some Message 2", $context = [123, 452345, 123]);
        $l = $this->logger->listLog($level, false, $t);
        $this->assertEquals(1, count($l));

    }

    public function testHelperFunctionsLogRightLevel()
    {
        $this->logger->alert(Logger::LOG_LEVEL_ALERT);
        $this->logger->error(Logger::LOG_LEVEL_ERROR);
        $this->logger->emergency(Logger::LOG_LEVEL_EMERGENCY);
        $this->logger->info(Logger::LOG_LEVEL_INFO);
        $this->logger->critical(Logger::LOG_LEVEL_CRITICAL);
        $this->logger->notice(Logger::LOG_LEVEL_NOTICE);
        $this->logger->warning(Logger::LOG_LEVEL_WARNING);
        $this->logger->debug(Logger::LOG_LEVEL_DEBUG);

        $l = $this->logger->listLog();
        $this->assertGreaterThan(0, count($l));
        foreach ($l as $e) {
            $this->assertEquals($e['level'], $e['message']);
        }
    }

    public function testGetContentAtTimeGetsRightContent()
    {
        $this->logger->log(Logger::LOG_LEVEL_ALERT, "Messsage 1", $context1 = [1, 2, 3]);
        sleep(2);
        $this->logger->log(Logger::LOG_LEVEL_ALERT, "Messsage 2", $context2 = [4, 5, 6]);
        $l = $this->logger->listLog();
        $this->assertEquals($context1, $this->logger->getContextAt($l[0]['time']));
        $this->assertEquals($context2, $this->logger->getContextAt($l[1]['time']));
    }
    public function testGetContentAtNonExistingTimeReturnsNull()
    {
        $this->logger->log(Logger::LOG_LEVEL_ALERT, "Messsage 2", $context2 = [4, 5, 6]);
        $this->assertNull( $this->logger->getContextAt(time()-100));

    }


    public function tearDown()
    {

        @$this->folder->delete(Folder::DELETE_FOLDER_RECURSIVE);
    }

}
