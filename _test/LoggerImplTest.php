<?php
require_once dirname(__FILE__) . '/../_class/LoggerImpl.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/3/12
 * Time: 9:37 PM
 * To change this template use File | Settings | File Templates.
 */
class LoggerImplTest extends PHPUnit_Framework_TestCase
{


    public function testLogWillThrowExceptionIfFirstParameterIsNotObject()
    {
        $logger = new LoggerImpl(dirname(__FILE__));
        $exceptionWasThrown = false;
        try {
            $logger->log('', 'TestMessage');
        } catch (Exception $e) {
            $this->assertInstanceOf('MalformedParameterException', $e);
            $exceptionWasThrown = true;
        }
        $this->assertTrue($exceptionWasThrown, 'Exception was not thrown');
    }


    public function testWillLogMessageToLogInRootDir()
    {
        $log = new FileImpl(dirname(__FILE__) . '/.log');
        if ($log->exists()) {
            $log->delete();
        }

        $logger = new LoggerImpl(dirname(__FILE__));
        $logger->log($this, 'TestMessage');
        $logger->log($this, 'TestMessage2');
        $log->getContents();
        $this->assertTrue($log->exists(), 'Log was not created');
        $this->assertNotEmpty($log->getContents(), 'Log was empty');
        $this->assertGreaterThan(0, strpos($log->getContents(), get_class($this)), 'Did not write class name');
        $this->assertGreaterThan(0, strpos($log->getContents(), 'TestMessage'), 'Did not write message');
        $this->assertGreaterThan(0, strpos($log->getContents(), 'TestMessage2'), 'Did not write second message');
        $log->delete();
    }


}
