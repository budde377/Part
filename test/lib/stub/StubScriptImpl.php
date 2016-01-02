<?php

namespace ChristianBudde\Part\test\stub;
use ChristianBudde\Part\util\script\Script;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/16/12
 * Time: 9:09 AM
 * To change this template use File | Settings | File Templates.
 */
class StubScriptImpl implements Script
{

    private $numRuns = 0;
    private $lastRun = 0;
    private $lastRunName = null;
    private $lastRunArgs = null;

    public $constructorArgs;

    function __construct()
    {
        $this->constructorArgs = func_get_args();
    }


    /**
     * This function runs the script
     * @param $name string
     * @param $args array | null
     */
    public function run($name, $args)
    {
        $this->numRuns++;
        $this->lastRun = microtime(true);
        $this->lastRunName = $name;
        $this->lastRunArgs = $args;
        sleep(1);
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

    /**
     * @return null | array
     */
    public function getLastRunName()
    {
        return $this->lastRunName;
    }

    /**
     * @return null | string
     */

    public function getLastRunArgs()
    {
        return $this->lastRunArgs;
    }
}
