<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 9:01 PM
 */

interface DumpFile extends File {


    /**
     * Creates a preamble to the file
     * @return void
     */
    public function create();

    /**
     * Dumps a variable to the dumpfile using print_r.
     * @param string $name
     * @param mixed $var
     * @return void
     */
    public function dumpVar($name, $var);

} 