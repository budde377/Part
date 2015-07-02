<?php
namespace ChristianBudde\Part\model\user;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/22/13
 * Time: 3:46 PM
 * To change this template use File | Settings | File Templates.
 */
use ChristianBudde\Part\model\VariablesImpl;
use ChristianBudde\Part\util\db\DB;

class UserVariablesImpl extends VariablesImpl implements \Serializable
{

    private $user;
    private $username;
    private $database;

    function __construct(DB $database, User $user)
    {

        $this->user = $user;
        $this->username = $user->getUsername();
        $this->database = $database;
        $this->setupVariable();
    }



    private function setupVariable()
    {
        $connection = $this->database->getConnection();
        $this->preparedUpdateValue = $connection->prepare("UPDATE UserVariables SET `value`= :value WHERE username = :username AND `key` = :key ");
        $this->preparedUpdateValue->bindParam('username', $this->username);
        $this->preparedSetValue = $connection->prepare("INSERT INTO UserVariables (`key`, `value`, username) VALUES (:key, :value , :username )");
        $this->preparedSetValue->bindParam('username', $this->username);
        $this->preparedRemoveKey = $connection->prepare("DELETE FROM UserVariables WHERE username = :username AND `key` = :key");
        $this->preparedRemoveKey->bindParam('username', $this->username);
        $this->preparedInitialize = $connection->prepare("SELECT `key`,`value` FROM UserVariables WHERE username = :username");
        $this->preparedInitialize->bindParam('username', $this->username);

    }


    public function getValue($key)
    {
        $this->updateUsername();
        return parent::getValue($key);
    }

    public function listKeys()
    {
        $this->updateUsername();
        return parent::listKeys();
    }

    public function setValue($key, $value)
    {
        if(!$this->user->exists()){
            return;
        }
        $this->updateUsername();
        parent::setValue($key, $value);
    }

    public function removeKey($key)
    {
        $this->updateUsername();
        parent::removeKey($key);
    }

    public function hasKey($key)
    {
        $this->updateUsername();
        return parent::hasKey($key);
    }

    public function getIterator()
    {
        $this->updateUsername();
        return parent::getIterator();
    }

    private function updateUsername()
    {
        $this->username= $this->user->getUsername();
    }



    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize([$this->user, $this->username, $this->database]);
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
        $this->user = $array[0];
        $this->username = $array[1];
        $this->database = $array[2];
        $this->setupVariable();
    }
}