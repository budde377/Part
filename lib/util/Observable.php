<?php
namespace ChristianBudde\Part\util;

/**
 * User: budde
 * Date: 6/17/12
 * Time: 4:22 PM
 */
interface Observable
{

    public function attachObserver(Observer $observer);

    public function detachObserver(Observer $observer);


}
