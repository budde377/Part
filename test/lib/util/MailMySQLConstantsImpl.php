<?php
namespace ChristianBudde\Part\test\util;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/4/14
 * Time: 11:53 PM
 */


class MailMySQLConstantsImpl extends MySQLConstantsImpl
{
    function __construct()
    {
        parent::__construct('localhost', 'test_postfix', 'test', 'x8Hp32vG4YEJwdK6');
    }
}