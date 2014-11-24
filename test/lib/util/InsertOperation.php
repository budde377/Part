<?php
namespace ChristianBudde\cbweb\test\util;
use PHPUnit_Extensions_Database_Operation_Insert;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 7:23 PM
 */

class InsertOperation extends PHPUnit_Extensions_Database_Operation_Insert
{
    public function execute(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
    {
        $connection->getConnection()->query("SET foreign_key_checks = 0");
        parent::execute($connection, $dataSet);
        $connection->getConnection()->query("SET foreign_key_checks = 1");
    }
}