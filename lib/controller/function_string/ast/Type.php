<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:23 PM
 */

namespace ChristianBudde\Part\controller\function_string\ast;


use ChristianBudde\Part\controller\json\Type as JType;

interface Type {

    /**
     * @return JType
     */
    public function toJSONTarget();

} 