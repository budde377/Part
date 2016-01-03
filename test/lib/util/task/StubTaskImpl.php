<?php

namespace ChristianBudde\Part\util\task;


/**
 * User: budde
 * Date: 5/16/12
 * Time: 9:09 AM
 */
class StubTaskImpl implements Task
{

    public $result;
    private $numRuns = 0;
    private $lastRun = 0;


    public $constructorArgs;

    function __construct()
    {
        $this->constructorArgs = func_get_args();
    }


    public function execute()
    {
        $this->numRuns++;
        $this->lastRun = microtime(true);
        sleep(1);
        return $this->result;
    }


    /**
     * @return int
     */
    public function getNumRuns()
    {
        return $this->numRuns;
    }

    /**
     * @return float
     */
    public function lastRunAt()
    {
        return $this->lastRun;
    }

}
