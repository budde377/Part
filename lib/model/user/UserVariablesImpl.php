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
use ChristianBudde\Part\util\Observable;
use ChristianBudde\Part\util\Observer;

class UserVariablesImpl extends VariablesImpl implements Observer
{

    private $user;
    private $username;

    function __construct(DB $database, User $user)
    {

        $this->user = $user;
        $user->attachObserver($this);
        $connection = $database->getConnection();
        $this->username = $user->getUsername();
        $preparedUpdateValue = $connection->prepare("UPDATE UserVariables SET `value`= :value WHERE username = :username AND `key` = :key ");
        $preparedUpdateValue->bindParam(':username', $this->username);
        $preparedSetValue = $connection->prepare("INSERT INTO UserVariables (`key`, `value`, username) VALUES (:key, :value , :username )");
        $preparedSetValue->bindParam(':username', $this->username);
        $preparedRemoveKey = $connection->prepare("DELETE FROM UserVariables WHERE username = :username AND `key` = :key");
        $preparedRemoveKey->bindParam(':username', $this->username);
        $prepInitialize = $connection->prepare("SELECT `key`,`value` FROM UserVariables WHERE username = :username");
        $prepInitialize->bindParam(':username', $this->username);

        parent::__construct($prepInitialize, $preparedRemoveKey, $preparedSetValue, $preparedUpdateValue);
    }

    public function setValue($key, $value)
    {
        if(!$this->user->exists()){
            return;
        }
        parent::setValue($key, $value);
    }


    public function onChange(Observable $subject, $changeType)
    {
        $this->username = $this->user->getUsername();
    }
}