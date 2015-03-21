<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/21/15
 * Time: 2:49 PM
 */

namespace ChristianBudde\Part\test\util;


use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;

class DeleteAllOperation extends \PHPUnit_Extensions_Database_Operation_DeleteAll{

    public function execute(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
    {
        $connection->getConnection()->query("SET foreign_key_checks = 0");
        parent::execute($connection, $dataSet);
        $connection->getConnection()->query("SET foreign_key_checks = 1");
    }

}