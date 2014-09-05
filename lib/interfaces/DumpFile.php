<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 9:01 PM
 */

interface DumpFile extends File {


    /**
     * Dumps a variable to the dumpfile using serialize.
     * @param mixed $var
     * @return void
     */
    public function writeSerialized($var);


    public function getUnSerializedContent();

} 