<?php
namespace ChristianBudde\cbweb\util;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/17/12
 * Time: 4:20 PM
 * To change this template use File | Settings | File Templates.
 */
interface Observer
{

    public function onChange(Observable $subject, $changeType);

}
