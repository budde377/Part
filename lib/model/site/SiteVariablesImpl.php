<?php
namespace ChristianBudde\Part\model\site;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/22/13
 * Time: 3:46 PM
 * To change this template use File | Settings | File Templates.
 */
use ChristianBudde\Part\model\VariablesImpl;
use ChristianBudde\Part\util\db\DB;

class SiteVariablesImpl extends VariablesImpl
{

    function __construct(DB $database)
    {

        $connection = $database->getConnection();
        $preparedUpdateValue = $connection->prepare("UPDATE SiteVariables SET `value`= :value WHERE `key` = :key ");
        $preparedSetValue = $connection->prepare("INSERT INTO SiteVariables (`key`, `value`) VALUES (:key, :value )");
        $preparedRemoveKey = $connection->prepare("DELETE FROM SiteVariables WHERE `key` = :key");
        $prepInitialize = $connection->prepare("SELECT `key`,`value` FROM SiteVariables ");

        parent::__construct($prepInitialize, $preparedRemoveKey, $preparedSetValue, $preparedUpdateValue);
    }

}