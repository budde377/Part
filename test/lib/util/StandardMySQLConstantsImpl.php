<?php
namespace ChristianBudde\Part\util;



/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/4/14
 * Time: 11:54 PM
 */
class StandardMySQLConstantsImpl extends MySQLConstantsImpl
{

    function __construct()
    {
        parent::__construct($GLOBALS['DB_HOST'], $GLOBALS['DB_NAME'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWORD']);
    }
}