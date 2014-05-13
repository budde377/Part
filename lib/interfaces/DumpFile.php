<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 9:01 PM
 */

interface DumpFile extends File {

    public function dumpVar($name, $var);

} 