<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/13/15
 * Time: 11:21 AM
 */

namespace ChristianBudde\Part\model;


use ChristianBudde\Part\util\db\DB;
use ChristianBudde\Part\util\Observable;
use ChristianBudde\Part\util\Observer;

abstract class BindParamObserverVariablesImpl extends VariablesImpl implements Observer
{

    private $observable;
    private $param;
    private $update_param;

    /**
     * @param DB $database
     * @param Observable $observable
     * @param string $param_name
     * @param callable $update_param
     * @param $updateValStmStr
     * @param $setValStmStr
     * @param $removeKeyStmStr
     * @param $initStmStr
     */
    function __construct(DB $database,
                         Observable $observable,
                         $param_name,
                         callable $update_param,
                         $updateValStmStr,
                         $setValStmStr,
                         $removeKeyStmStr,
                         $initStmStr)
    {
        $this->update_param = $update_param;
        $this->observable = $observable;

        $connection = $database->getConnection();

        $this->updateParam();
        $observable->attachObserver($this);

        $preparedUpdateValue = $connection->prepare($updateValStmStr);
        $preparedUpdateValue->bindParam($param_name, $this->param);
        $preparedSetValue = $connection->prepare($setValStmStr);
        $preparedSetValue->bindParam($param_name, $this->param);
        $preparedRemoveKey = $connection->prepare($removeKeyStmStr);
        $preparedRemoveKey->bindParam($param_name, $this->param);
        $prepInitialize = $connection->prepare($initStmStr);
        $prepInitialize->bindParam($param_name, $this->param);

        parent::__construct($prepInitialize, $preparedRemoveKey, $preparedSetValue, $preparedUpdateValue);
    }

    public function onChange(Observable $subject, $changeType)
    {
        $this->updateParam();
    }

    private function updateParam()
    {
        $updater = $this->update_param;
        $this->param = $updater($this->observable);
    }
}