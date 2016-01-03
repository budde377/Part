<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/6/15
 * Time: 6:40 PM
 */

namespace ChristianBudde\Part\util\task;



use ChristianBudde\Part\exception\ForceExitException;

class ConstructorStubTaskImpl {


    function __construct()
    {
        throw new ForceExitException(func_get_args());
    }
}