<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 10:27 AM
 */

interface LogFile extends File{

    const LOG_LEVEL_DEBUG = 1;
    const LOG_LEVEL_NOTICE = 2;
    const LOG_LEVEL_WARNING = 4;
    const LOG_LEVEL_ERROR = 8;

    /**
     * Will log a message and write to file.
     * @param string $message
     * @param int $level
     * @return void
     */
    public function log($message, $level);

    /**
     * @param int $level Use bitwise or to show multiple levels.
     * @return array Will return an list ordered by log-time containing three indices: level, message and unix-time.
     */
    public function listLog($level);
} 