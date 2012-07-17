<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/15/12
 * Time: 8:16 AM
 */
interface JSRegister
{

    /**
     * @abstract
     * Will register a file
     * @param JSFile $file
     * @return void
     */
    public function registerJSFile(JSFile $file);


    /**
     * @abstract
     * Will return array with no duplicates
     * @return array
     */
    public function getRegisteredFiles();


}
