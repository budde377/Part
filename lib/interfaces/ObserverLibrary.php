<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/9/14
 * Time: 3:06 PM
 */

interface ObserverLibrary {

    public function registerObserver(Observer $observer);

    public function removeObserver(Observer $observer);

    public function callObservers($eventType);

} 