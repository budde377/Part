<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/17/14
 * Time: 4:57 PM
 */

class StubDumpFileImpl extends FileImpl implements DumpFile{
    function __construct()
    {
        parent::__construct("");
    }

    /**
     * Creates a preamble to the file
     * @return void
     */
    public function create()
    {

    }

    /**
     * Dumps a variable to the dumpfile using print_r.
     * @param string $name
     * @param mixed $var
     * @return void
     */
    public function dumpVar($name, $var)
    {

    }
}