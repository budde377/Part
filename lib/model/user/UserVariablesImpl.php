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

class UserVariablesImpl extends VariablesImpl
{

    private $user;

    function __construct(DB $database, User $user)
    {

        $this->user = $user;
        $connection = $database->getConnection();
        $username = $user->getUsername();
        $preparedUpdateValue = $connection->prepare("UPDATE UserVariables SET `value`= :value WHERE username = :username AND `key` = :key ");
        $preparedUpdateValue->bindParam(':username', $username);
        $preparedSetValue = $connection->prepare("INSERT INTO UserVariables (`key`, `value`, username) VALUES (:key, :value , :username )");
        $preparedSetValue->bindParam(':username', $username);
        $preparedRemoveKey = $connection->prepare("DELETE FROM UserVariables WHERE username = :username AND `key` = :key");
        $preparedRemoveKey->bindParam(':username', $username);
        $prepInitialize = $connection->prepare("SELECT `key`,`value` FROM UserVariables WHERE username = :username");
        $prepInitialize->bindParam(':username', $username);

        parent::__construct($prepInitialize, $preparedRemoveKey, $preparedSetValue, $preparedUpdateValue);
    }

    public function setValue($key, $value)
    {
        if(!$this->user->exists()){
            return;
        }
        parent::setValue($key, $value);
    }


}