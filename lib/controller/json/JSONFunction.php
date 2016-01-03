<?php
namespace ChristianBudde\Part\controller\json;


/**
 * User: budde
 * Date: 22/01/13
 * Time: 10:00
 */
interface JSONFunction extends Target, Program
{

    /**
     * Will return a numerical array of arguments
     * @return array
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


}
