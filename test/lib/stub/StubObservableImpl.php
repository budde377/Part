<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/9/14
 * Time: 3:10 PM
 */
namespace ChristianBudde\Part\test\stub;

use ChristianBudde\Part\util\Observable;
use ChristianBudde\Part\util\Observer;

class StubObservableImpl implements Observable
{

    public function attachObserver(Observer $observer)
    {

    }

    public function detachObserver(Observer $observer)
    {

    }
}