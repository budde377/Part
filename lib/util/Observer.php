<?php
namespace ChristianBudde\Part\util;

/**
 * User: budde
 * Date: 6/17/12
 * Time: 4:20 PM
 */
interface Observer
{

    public function onChange(Observable $subject, $changeType);

}
