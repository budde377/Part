<?php
namespace ChristianBudde\cbweb;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/9/14
 * Time: 3:06 PM
 */

class ObserverLibraryImpl implements ObserverLibrary {

    private $observable;
    private $observers = array();

    function __construct(Observable $observable)
    {
        $this->observable = $observable;
    }


    public function registerObserver(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    public function removeObserver(Observer $observer)
    {
        foreach($this->observers as $k=>$v){
            if($v === $observer){
                unset($this->observers[$k]);
            }
        }
    }

    public function callObservers($eventType)
    {
        foreach($this->observers as $observer){
            /** @var $observer Observer */
            $observer->onChange($this->observable, $eventType);
        }
    }
}