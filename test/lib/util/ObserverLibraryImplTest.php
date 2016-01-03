<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/9/14
 * Time: 3:09 PM
 */
namespace ChristianBudde\Part\util;

use PHPUnit_Framework_TestCase;

class ObserverLibraryImplTest extends PHPUnit_Framework_TestCase
{

    /** @var  ObserverLibraryImpl */
    private $observerLibrary;
    private $observable;
    /** @var  StubObserverImpl */
    private $observer;
    /** @var  StubObserverImpl */
    private $observer2;

    public function setUp()
    {

        $this->observable = new StubObservableImpl();
        $this->observerLibrary = new ObserverLibraryImpl($this->observable);
        $this->observer = new StubObserverImpl();
        $this->observer2 = new StubObserverImpl();
    }


    public function testAddingToObserverLibraryWillSave()
    {
        $this->observerLibrary->registerObserver($this->observer);
        $this->observerLibrary->callObservers(1);
        $this->assertTrue($this->observer->hasBeenCalled());
        $this->assertTrue($this->observer->getLastCallSubject() === $this->observable);
        $this->assertEquals(1, $this->observer->getLastCallType());

    }


    public function testAddingMoreToObserverLibraryWillSave()
    {
        $this->observerLibrary->registerObserver($this->observer);
        $this->observerLibrary->registerObserver($this->observer2);
        $this->observerLibrary->callObservers(1);
        $this->assertTrue($this->observer->hasBeenCalled());
        $this->assertTrue($this->observer->getLastCallSubject() === $this->observable);
        $this->assertEquals(1, $this->observer->getLastCallType());
        $this->assertTrue($this->observer2->hasBeenCalled());
        $this->assertTrue($this->observer2->getLastCallSubject() === $this->observable);
        $this->assertEquals(1, $this->observer2->getLastCallType());

    }

    public function testOneCanUnSubscribe()
    {
        $this->observerLibrary->registerObserver($this->observer);
        $this->observerLibrary->registerObserver($this->observer2);
        $this->observerLibrary->registerObserver($this->observer2);
        $this->observerLibrary->removeObserver($this->observer2);
        $this->observerLibrary->callObservers(1);
        $this->assertTrue($this->observer->hasBeenCalled());
        $this->assertTrue($this->observer->getLastCallSubject() === $this->observable);
        $this->assertEquals(1, $this->observer->getLastCallType());
        $this->assertFalse($this->observer2->hasBeenCalled());

    }


    public function testCallEmptyObserverLibraryIsOK()
    {
        $this->observerLibrary->callObservers(1);
    }

}