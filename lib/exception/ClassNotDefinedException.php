<?php
namespace ChristianBudde\Part\exception;

use Exception;

/**
 * User: budde
 * Date: 5/14/12
 * Time: 5:32 PM
 */
class ClassNotDefinedException extends Exception
{

    public function __construct($className)
    {
        parent::__construct('ClassNotDefinedException: Class "' . $className . '" was not defined!');
    }

}
