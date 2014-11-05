<?php
namespace ChristianBudde\cbweb\util\file;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/17/14
 * Time: 4:50 PM
 */

class StubLogFileImpl extends FileImpl implements LogFile{
    function __construct()
    {
        parent::__construct("");
    }


    /**
     * Will log a message and write to file.
     * @param string $message
     * @param int $level
     * @param bool | DumpFile $createDumpFile
     * @return int
     */
    public function log($message, $level, &$createDumpFile = false)
    {
        if($createDumpFile !== false){
            $createDumpFile = new StubDumpFileImpl();
        }
        return 0;
    }

    /**
     * @param int $level Use bitwise or to show multiple levels.
     * @param int $time The earliest time to retrieve.
     * @return array Will return an list ordered by log-time containing three indices: level, message and unix-time. Default is all
     */
    public function listLog($level = null, $time = 0)
    {
        return array();
    }

    /**
     * Will clear the log and remove all dump files.
     * @return void
     */
    public function clearLog()
    {

    }
}