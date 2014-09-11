<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/9/14
 * Time: 3:10 PM
 */
use ChristianBudde\cbweb\Observer;
use ChristianBudde\cbweb\Observable;

class StubObservableImpl implements Observable{

    public function attachObserver(Observer $observer)
    {

    }

    public function detachObserver(Observer $observer)
    {

    }
}