<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 4:07 PM
 */

namespace ChristianBudde\Part\controller\ajax;


interface TypeHandlerGenerator {


    /**
     * @return TypeHandler
     */
    public function generateTypeHandler();

}