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

class PageVariablesImpl extends VariablesImpl
{

    private $page_id;

    function __construct(DB $database, Page $page)
    {
        $this->page_id = $page->getID();
        $this->page = $page;
        $connection = $database->getConnection();
        $preparedUpdateValue = $connection->prepare("UPDATE PageVariables SET `value`= :value WHERE page_id = :page_id AND `key` = :key");
        $preparedUpdateValue->bindParam('page_id', $this->page_id);
        $preparedSetValue = $connection->prepare("INSERT INTO PageVariables (`key`, `value`, page_id) VALUES (:key, :value, :page_id )");
        $preparedSetValue->bindParam('page_id', $this->page_id);
        $preparedRemoveKey = $connection->prepare("DELETE FROM PageVariables WHERE page_id = :page_id AND `key` = :key");
        $preparedRemoveKey->bindParam('page_id', $this->page_id);
        $prepInitialize = $connection->prepare("SELECT `key`,`value` FROM PageVariables WHERE page_id = :page_id");
        $prepInitialize->bindParam('page_id', $this->page_id);

        parent::__construct($prepInitialize, $preparedRemoveKey, $preparedSetValue, $preparedUpdateValue);
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


}