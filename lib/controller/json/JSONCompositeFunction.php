<?php
namespace ChristianBudde\cbweb\controller\json;



/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 9:06 PM
 */

interface JSONCompositeFunction extends JSONProgram{

    /**
     * @return array
     */
    public function listFunctions();


    /**
     * @param JSONFunction $function
     * @return void
     */
    public function appendFunction(JSONFunction $function);

    /**
     * @param JSONFunction $function
     * @return void
     */

    public function prependFunction(JSONFunction $function);

    /**
     * @param JSONFunction $function
     * @return void
     */
    public function removeFunction(JSONFunction $function);

} 