<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 10:00
 * To change this template use File | Settings | File Templates.
 */
interface JSONFunction
{
    /**
     * Will return a numerical array of arguments as strings
     * @return Array
     */
    public function getArgs();

    /**
     * Will return the name of the function as a String
     * @return String
     */
    public function getName();

    /**
     * @param array $args Associative array containing arg name and value
     * @return JSONResponse
     */
    public function call(array $args = array());

}
