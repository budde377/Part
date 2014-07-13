<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/4/14
 * Time: 11:53 PM
 */


class CustomDatabasePostfixTestCase extends CustomDatabaseTestCase{
    public static function setUpBeforeClass(){
        self::$mysqlOptions = new MailMySQLConstantsImpl();
        parent::setUpBeforeClass();
    }

}