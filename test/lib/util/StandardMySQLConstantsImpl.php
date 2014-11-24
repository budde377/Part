<?php
namespace ChristianBudde\cbweb\test\util;



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
        parent::__construct('localhost', 'test_cms', 'test', 'x8Hp32vG4YEJwdK6');
    }
}