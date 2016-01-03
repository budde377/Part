<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/17/12
 * Time: 4:27 PM
 * To change this template use File | Settings | File Templates.
 */
namespace ChristianBudde\Part\util;




class StubObserverImpl implements Observer
{

    private $hasBeenCalled = false;
    /** @var $lastCallSubject null | \ChristianBudde\Part\util\Observable */
    private $lastCallSubject = null;
    private $lastCallType = null;

    public function onChange(Observable $subject, $changeType)
    {

        $this->hasBeenCalled = true;
        $this->lastCallType = $changeType;
        $this->lastCallSubject = $subject;

    }

    /**
     * @return bool
     */
    public function hasBeenCalled()
    {
        return $this->hasBeenCalled;
    }

    public function getLastCallSubject()
    {
        return $this->lastCallSubject;
    }

    public function getLastCallType()
    {
        return $this->lastCallType;
    }
}
