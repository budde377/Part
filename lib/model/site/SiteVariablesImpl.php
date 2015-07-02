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

class SiteVariablesImpl extends VariablesImpl implements \Serializable
{

    private $database;

    function __construct(DB $database)
    {
        $this->database = $database;
        $connection = $database->getConnection();
        $preparedUpdateValue = $connection->prepare("UPDATE SiteVariables SET `value`= :value WHERE `key` = :key ");
        $preparedSetValue = $connection->prepare("INSERT INTO SiteVariables (`key`, `value`) VALUES (:key, :value )");
        $preparedRemoveKey = $connection->prepare("DELETE FROM SiteVariables WHERE `key` = :key");
        $prepInitialize = $connection->prepare("SELECT `key`,`value` FROM SiteVariables ");

        parent::__construct($prepInitialize, $preparedRemoveKey, $preparedSetValue, $preparedUpdateValue);
    }


    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->database);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->__construct(unserialize($serialized));
    }
}