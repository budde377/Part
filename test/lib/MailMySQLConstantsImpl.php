<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/4/14
 * Time: 11:53 PM
 */


class MailMySQLConstantsImpl extends MySQLConstantsImpl{
    function __construct(){
        parent::__construct('10.8.0.1',  'postfix_test', 'postfixtestadmin', 'tMt86cqdzqQfB8tJ');
    }
}