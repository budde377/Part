<?php
namespace ChristianBudde\cbweb\util\file;



/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 10:30 AM
 */
class LogFileImpl extends FileImpl implements LogFile
{

    private $dumparray = array();

    /**
     * Will log a message and write to file.
     * @param string $message
     * @param int $level
     * @param bool|DumpFile $createDumpFile
     * @return int
     */
    public function log($message, $level, &$createDumpFile = false)
    {

        $this->create();
        $array = array($level, $t = time(), $message);

        if($createDumpFile !== false){
            $createDumpFile = new DumpFileImpl($this->getParentFolder()->getAbsolutePath()."/dump-".uniqid());
            $this->dumparray[$array[] = $createDumpFile->getFilename()] = $createDumpFile;
        }

        fputcsv($r = $this->getResource(), $array);
        fclose($r);

        return $t;
    }

    /**
     * @param int $level Use bitwise or to show multiple levels.
     * @param int $time The earliest time to retrieve.
     * @return array Will return an list ordered by log-time containing three indices: level, message and unix-time. Default is all
     */
    public function listLog($level = null, $time = 0)
    {
        if(!$this->exists()){
            return array();
        }
        $ar = array();

        $r = $this->getResource();
        while($e = fgetcsv($r)){
            if(($level == null || ($e[0] & $level)) && $time <= $e[1]){
                $a = array("level"=>$e[0], "time"=>$e[1], "message"=>$e[2]);
                if(isset($e[3])){
                    if(!isset($this->dumparray[$e[3]])){
                        $this->dumparray[$e[3]] = new DumpFileImpl($this->getParentFolder()->getAbsolutePath()."/".$e[3]);
                    }
                    $a["dumpfile"] = $this->dumparray[$e[3]];
                }
                $ar[] = $a;
            }
        }
        fclose($r);
        return $ar;
    }

    /**
     * Will clear the log and remove all dump files.
     * @return void
     */
    public function clearLog()
    {
        $this->listLog();
        foreach($this->dumparray as $df){
            /** @var $df DumpFile */
            $df->delete();
        }
        $this->setAccessMode(File::FILE_MODE_RW_TRUNCATE_FILE_TO_ZERO_LENGTH);
        $this->write("");
        $this->setAccessMode(File::FILE_MODE_RW_POINTER_AT_END);

    }

    private function create()
    {
        $f = $this->getParentFolder();
        if($f->exists()){
            return;
        }
        $f->create(true);
    }
}