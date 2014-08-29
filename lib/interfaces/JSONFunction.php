<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 10:00
 * To change this template use File | Settings | File Templates.
 */
interface JSONFunction extends JSONTarget
{

    /**
     * @return JSONTarget
     */
    public function getTarget();

    /**
     * Will return a numerical array of arguments
     * @return Array
     */
    public function getArgs();

    /**
     * Will return argument at index given
     * @param $num
     * @return mixed
     */
    public function getArg($num);

    /**
     * Will return the name of the function as a String
     * @return String
     */
    public function getName();

    /**
     * Will set an argument with value
     * @param int $num
     * @param mixed $value
     * @return void
     */
    public function setArg($num, $value);

    /**
     * Clears arguments
     * @return void
     */
    public function clearArguments();


    public function getId();

    public function setId($id);
}
