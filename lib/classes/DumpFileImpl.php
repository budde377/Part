<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 9:02 PM
 */

class DumpFileImpl extends FileImpl implements DumpFile{


    public function dumpVar($name, $var)
    {

        $this->write("\n## $name ##\n");
        $this->write(print_r($var, true)."\n");

    }

    public function create()
    {
        if($this->size() > 0){
            return;
        }
        $this->write("# About #\n");
        $this->write("This dump file was created on ". date("j-n-Y") . " at " .date("H:i:s").".\n\n");
        $this->write("# Dumped variables #\n");

    }
}