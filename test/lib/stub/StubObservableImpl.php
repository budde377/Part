<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/9/14
 * Time: 3:10 PM
 */
namespace ChristianBudde\cbweb\test\stub;

use ChristianBudde\cbweb\util\Observer;
use ChristianBudde\cbweb\util\Observable;

class StubObservableImpl implements Observable
{

    public function attachObserver(Observer $observer)
    {

    }

    public function detachObserver(Observer $observer)
    {

    }
}