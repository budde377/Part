<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 4:18 PM
 */

namespace ChristianBudde\Part\test\stub;


use ChristianBudde\Part\controller\ajax\TypeHandlerGenerator;

class StubAJAXTypeHandlerGeneratorImpl implements TypeHandlerGenerator{


    private static $handler ;

    public function generateTypeHandler()
    {
        return self::$handler;
    }


    public static function setHandler($handler){
        self::$handler = $handler;
    }
}