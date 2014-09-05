<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/3/12
 * Time: 9:38 PM
 * To change this template use File | Settings | File Templates.
 */
class LoggerImpl implements Logger
{

    private $logFile;

    function __construct($filePath)
    {
        $this->logFile = $filePath == ""?new StubLogFileImpl():new LogFileImpl($filePath);
    }


    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $this->log(Logger::LOG_LEVEL_EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->log(Logger::LOG_LEVEL_ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $this->log(Logger::LOG_LEVEL_CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->log(Logger::LOG_LEVEL_ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $this->log(Logger::LOG_LEVEL_WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $this->log(Logger::LOG_LEVEL_NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->log(Logger::LOG_LEVEL_INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $this->log(Logger::LOG_LEVEL_DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $dumpFile = $this->logFile->log($message, $level, $context != []);
        if($dumpFile != null){
            $dumpFile->writeSerialized($context);
        }
    }

    /**
     * Use boolean or to combine which loglevels you whish to list.
     * @param int $level
     * @param bool $includeContext If false context will not be included in result.
     * @param int $time The earliest returned entry will be after this value
     * @return mixed
     */
    public function listLog($level = Logger::LOG_LEVEL_ALL, $includeContext = true, $time = 0)
    {
        $list = $this->logFile->listLog($level, $time);
        $result = [];
        foreach($list as $entry){
            if(isset($entry['dumpfile'])){
                /** @var DumpFile $dumpFile */
                $dumpFile = $entry['dumpfile'];

                $entry['context'] = $dumpFile->getUnSerializedContent()[0];


                unset($entry['dumpfile']);

            }
            $result[] = $entry;

        }

        return  $result;
    }
}
