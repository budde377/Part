<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/24/14
 * Time: 10:08 PM
 */

namespace ChristianBudde\Part\controller\function_string\ast;

use ChristianBudde\Part\controller\json\Program as JProgram;

interface Program extends ScalarArrayProgram{



    /**
     * @return Type
     */
    public function getType();

    /**
     * @return JProgram
     */
    public function toJSONProgram();

}