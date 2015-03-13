<?php
namespace ChristianBudde\Part\model\page;

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

class PageVariablesImpl extends BindParamObserverVariablesImpl implements Observer
{

    function __construct(DB $database, Page $page)
    {

        $this->page = $page;
        parent::__construct(
            $database,
            $page,
            ":page_id",
            function (Page $page) {
                return $page->getID();
            },
            function (Page $page) {
                return $page->exists();
            },
            "UPDATE PageVariables SET `value`= :value WHERE page_id = :page_id AND `key` = :key ",
            "INSERT INTO PageVariables (`key`, `value`, page_id) VALUES (:key, :value, :page_id )",
            "DELETE FROM PageVariables WHERE page_id = :page_id AND `key` = :key",
            "SELECT `key`,`value` FROM PageVariables WHERE page_id = :page_id");
    }



}