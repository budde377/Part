<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 10:30 AM
 */
class LogFileImpl extends FileImpl implements LogFile
{

    /**
     * @param int $level Use bitwise or to show multiple levels.
     * @return array Will return an list ordered by log-time containing three indices: level, message and unix-time. Default is all
     */
    public function listLog($level = LogFile::LOG_LEVEL_ALL)
    {


        $ar = array();
        $r = $this->getResource();
        while($e = fgetcsv($r)){
            if($e[0] & $level){
                $a = array("level"=>$e[0], "time"=>$e[1], "message"=>$e[2]);
                if(isset($e[3])){
                    $a["dumpfile"] = $e[3];
                }
                $ar[] = $a;
            }
        }
        fclose($r);
        return $ar;
    }

    /**
     * Will log a message and write to file.
     * @param string $message
     * @param int $level
     * @param bool $createDumpFile
     * @return null | DumpFile
     */
    public function log($message, $level, $createDumpFile = false)
    {
        $array = array($level, time(), $message);
        $ret = null;
        if($createDumpFile){
            $ret = new DumpFileImpl($this->getParentFolder()->getAbsolutePath()."/dump-".uniqid());
            $ret->write("DUMP FILE CREATED AT ".date("d-m-Y H:i:S"));
            $array[] = $ret->getFilename();

        }

        fputcsv($r = $this->getResource(), $array);
        fclose($r);

        return $ret;
    }
}