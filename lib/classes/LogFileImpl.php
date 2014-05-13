<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 10:30 AM
 */

class LogFileImpl extends FileImpl implements LogFile{


    /**
     * Will log a message and write to file.
     * @param string $message
     * @param int $level
     * @return void
     */
    public function log($message, $level)
    {
        $array = array("message" => $message, "level"=>$level, "time"=>time());
        $this->write(serialize($array));
    }

    /**
     * @param int $level Use bitwise or to show multiple levels.
     * @return array Will return an list ordered by log-time containing three indices: level, message and unix-time.
     */
    public function listLog($level)
    {
        // TODO: Implement listLog() method.
    }
}