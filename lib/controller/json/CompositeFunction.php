<?php
namespace ChristianBudde\cbweb\controller\json;



/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 9:06 PM
 */

interface CompositeFunction extends Program{

    /**
     * @return JSONFunction[]
     */
    public function listFunctions();

} 