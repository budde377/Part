<?php
namespace ChristianBudde\Part\util\file;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 9:02 PM
 */

class DumpFileImpl extends FileImpl implements DumpFile{

    /**
     * Dumps a variable to the dumpfile using serialize.
     * @param mixed $var
     * @return void
     */
    public function writeSerialized($var)
    {
        $data = (($c = $this->getUnSerializedContent()) == ""?[]:$c);
        $data[] = $var;
        $this->delete();
        $this->write(serialize($data));

    }

    public function getUnSerializedContent()
    {
        return unserialize($this->getContents());
    }
}