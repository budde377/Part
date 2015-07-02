<?php
namespace ChristianBudde\Part\model\page;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/22/13
 * Time: 3:46 PM
 * To change this template use File | Settings | File Templates.
 */
use ChristianBudde\Part\model\VariablesImpl;
use ChristianBudde\Part\util\db\DB;

class PageVariablesImpl extends VariablesImpl implements \Serializable
{

    private $page_id;
    private $page;
    private $database;

    function __construct(DB $database, Page $page)
    {
        $this->page_id = $page->getID();
        $this->page = $page;
        $this->database = $database;

        $this->setupVariable();
    }

    public function getValue($key)
    {
        $this->updateId();
        return parent::getValue($key);
    }

    public function listKeys()
    {
        $this->updateId();
        return parent::listKeys();
    }

    public function setValue($key, $value)
    {
        if(!$this->page->exists()){
            return;
        }
        $this->updateId();
        parent::setValue($key, $value);
    }

    public function removeKey($key)
    {
        $this->updateId();
        parent::removeKey($key);
    }

    public function hasKey($key)
    {
        $this->updateId();
        return parent::hasKey($key);
    }

    public function getIterator()
    {
        $this->updateId();
        return parent::getIterator();
    }

    private function updateId()
    {
        $this->page_id = $this->page->getID();
    }

    private function setupVariable()
    {
        $connection = $this->database->getConnection();
        $this->preparedUpdateValue= $connection->prepare("UPDATE PageVariables SET `value`= :value WHERE page_id = :page_id AND `key` = :key");
        $this->preparedUpdateValue->bindParam('page_id', $this->page_id);
        $this->preparedSetValue = $connection->prepare("INSERT INTO PageVariables (`key`, `value`, page_id) VALUES (:key, :value, :page_id )");
        $this->preparedSetValue->bindParam('page_id', $this->page_id);
        $this->preparedRemoveKey = $connection->prepare("DELETE FROM PageVariables WHERE page_id = :page_id AND `key` = :key");
        $this->preparedRemoveKey->bindParam('page_id', $this->page_id);
        $this->preparedInitialize= $connection->prepare("SELECT `key`,`value` FROM PageVariables WHERE page_id = :page_id");
        $this->preparedInitialize->bindParam('page_id', $this->page_id);

    }


    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize([$this->database, $this->page, $this->page_id]);
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
        $array = unserialize($serialized);
        $this->database = $array[0];
        $this->page = $array[1];
        $this->page_id = $array[2];
        $this->setupVariable();
    }
}