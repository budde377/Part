<?php
namespace ChristianBudde\cbweb\test\util;

use ChristianBudde\cbweb\test\util\MySQLConstantsImpl;

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
        parent::__construct('10.8.0.1', 'cms2012testdb', 'cms2012', 'plovMand50');
    }
}