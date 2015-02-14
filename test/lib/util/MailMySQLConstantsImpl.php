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
        parent::__construct($GLOBALS['DB_MAIL_HOST'], $GLOBALS['DB_MAIL_NAME'], $GLOBALS['DB_MAIL_USER'], $GLOBALS['DB_MAIL_PASSWORD']);
    }
}