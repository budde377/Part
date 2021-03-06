<?php
namespace ChristianBudde\Part\log;
use ChristianBudde\Part\controller\ajax\TypeHandlerGenerator;

/**
 * User: budde
 * Date: 6/3/12
 * Time: 9:31 PM
 */
interface Logger extends TypeHandlerGenerator
{


    const LOG_LEVEL_EMERGENCY = 1;
    const LOG_LEVEL_ALERT     = 2;
    const LOG_LEVEL_CRITICAL  = 4;
    const LOG_LEVEL_ERROR     = 8;
    const LOG_LEVEL_WARNING   = 16;
    const LOG_LEVEL_NOTICE    = 32;
    const LOG_LEVEL_INFO      = 64;
    const LOG_LEVEL_DEBUG     = 128;

    const LOG_LEVEL_ALL = 255;

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return int
     */
    public function emergency($message, array $context = []);

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return int
     */
    public function alert($message, array $context = []);

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return int
     */
    public function critical($message, array $context = []);

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return int
     */
    public function error($message, array $context = []);

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return int
     */
    public function warning($message, array $context = []);

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return int
     */
    public function notice($message, array $context = []);

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return int
     */
    public function info($message, array $context = []);

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return int
     */
    public function debug($message, array $context = []);

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return int
     */
    public function log($level, $message, array $context = []);

    /**
     * Use boolean or to combine which loglevels you wish to list.
     * Returns an array with entries
     * @param int $level
     * @param bool $includeContext If false context will not be included in result.
     * @param int $time The earliest returned entry will be after this value
     * @return array
     */
    public function listLog($level = Logger::LOG_LEVEL_ALL, $includeContext = true, $time = 0);

    /**
     * Returns the context corresponding to the time given.
     * @param int $time
     * @return array
     */
    public function getContextAt($time);

    /**
     * Clears the log
     * @return void
     */
    public function clearLog();
}
