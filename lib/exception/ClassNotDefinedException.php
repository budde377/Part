<?php
namespace ChristianBudde\cbweb\exception;

use Exception;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/14/12
 * Time: 5:32 PM
 * To change this template use File | Settings | File Templates.
 */
class ClassNotDefinedException extends Exception
{

    public function __construct($className)
    {
        parent::__construct('ClassNotDefinedException: Class "' . $className . '" was not defined!');
    }

}
