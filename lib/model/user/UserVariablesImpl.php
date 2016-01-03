<?php
namespace ChristianBudde\Part\model\user;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/22/13
 * Time: 3:46 PM
 * To change this template use File | Settings | File Templates.
 */
use ChristianBudde\Part\model\BindParamObserverVariablesImpl;
use ChristianBudde\Part\util\db\DB;
use ChristianBudde\Part\util\Observer;

class UserVariablesImpl extends BindParamObserverVariablesImpl implements Observer
{


    private $user;

    function __construct(DB $database, User $user)
    {

        $this->user = $user;

        parent::__construct(
            $database,
            $user,
            ':username',
            function (User $user) {
                return $user->getUsername();
            },
            function (User $user){
                return $user->exists();
            },
            "UPDATE UserVariables SET `value`= :value WHERE username = :username AND `key` = :key ",
            "INSERT INTO UserVariables (`key`, `value`, username) VALUES (:key, :value , :username )",
            "DELETE FROM UserVariables WHERE username = :username AND `key` = :key",
            "SELECT `key`,`value` FROM UserVariables WHERE username = :username");
    }



}