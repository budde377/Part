<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/30/14
 * Time: 5:49 PM
 */

namespace ChristianBudde\cbweb\test\stub;


use ChristianBudde\cbweb\model\updater\Updater;

class StubUpdaterImpl implements Updater{

    /**
     * Will check if there exists a new update.
     * This must be blocking if using external program such as git.
     * @param bool $quick If TRUE will do a quick check. May contain false positives.
     * @return bool Return TRUE on existing new update, else FALSE
     */
    public function checkForUpdates($quick = false)
    {
        return false;
    }

    /**
     * Will update the system.
     * As checkForUpdates() this must also be blocking.
     * @return void
     */
    public function update()
    {

    }

    /**
     * Will return timestamp of last checked
     * @return int
     */
    public function lastChecked()
    {
        return 0;

    }

    /**
     * Last update
     * @return int Timestamp of last update
     */
    public function lastUpdated()
    {
        return 0;
    }

    /**
     * @return string This should return a string containing some representation for the version for support reference
     */
    public function getVersion()
    {
        return "v0";
    }
}