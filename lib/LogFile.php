<?php
namespace ChristianBudde\cbweb;/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 10:27 AM
 */

interface LogFile extends File{

    /**
     * Will log a message and write to file.
     * @param string $message
     * @param int $level
     * @param bool $createDumpFile
     * @return null | DumpFile
     */
    public function log($message, $level, $createDumpFile = false);

    /**
     * @param int $level Use bitwise or to show multiple levels.
     * @param int $time The earliest time to retrieve.
     * @return array Will return an list ordered by log-time containing three indices: level, message and unix-time. Default is all
     */
    public function listLog($level , $time = 0);

    /**
     * Will clear the log and remove all dump files.
     * @return void
     */
    public function clearLog();

}