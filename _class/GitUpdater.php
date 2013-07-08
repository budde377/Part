<?php
require_once dirname(__FILE__).'/../_interface/Updater.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/05/13
 * Time: 21:51
 * To change this template use File | Settings | File Templates.
 */

class GitUpdater implements Updater{

    /**
     * Will check if there exists a new update.
     * This must be blocking if using external program such as git.
     * @return bool Return TRUE on existing new update, else FALSE
     */
    public function checkForUpdates()
    {
        // TODO: Implement checkForUpdates() method.
    }

    /**
     * Will update the system.
     * As checkForUpdates() this must also be blocking.
     * @return void
     */
    public function update()
    {
        // TODO: Implement update() method.
    }

    /**
     * Last check for updates.
     * @return int Timestamp of last check
     */
    public function lastChecked()
    {
        // TODO: Implement lastChecked() method.
    }

    /**
     * Last update
     * @return int Timestamp of last update
     */
    public function lastUpdated()
    {
        // TODO: Implement lastUpdated() method.
    }
}