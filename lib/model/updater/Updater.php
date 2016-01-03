<?php
namespace ChristianBudde\Part\model\updater;
use ChristianBudde\Part\controller\ajax\TypeHandlerGenerator;
use ChristianBudde\Part\model\user\User;

/**
 * User: budde
 * Date: 10/05/13
 * Time: 21:45
 */

interface Updater extends TypeHandlerGenerator{
    /**
     * Will check if there exists a new update.
     * This must be blocking if using external program such as git.
     * @param bool $quick If TRUE will do a quick check. May contain false positives.
     * @return bool Return TRUE on existing new update, else FALSE
     */
    public function checkForUpdates($quick = false);

    /**
     * Will update the system.
     * As checkForUpdates() this must also be blocking.
     * @return void
     */
    public function update();


    /**
     * Will return timestamp of last checked
     * @return int
     */
    public function lastChecked();

    /**
     * Last update
     * @return int Timestamp of last update
     */
    public function lastUpdated();

    /**
     * @return string This should return a string containing some representation for the version for support reference
     */
    public function getVersion();

    /**
     * Given a user it will enable update check on login
     * @param User $user
     * @return void
     */
    public function allowCheckOnLogin(User $user);

    /**
     * Given a user it will disable update check on login
     * @param User $user
     * @return void
     */
    public function disallowCheckOnLogin(User $user);

    /**
     * Given a user it will return true iff updates are enabled on login.
     * Default is true.
     * @param User $user
     * @return bool
     */
    public function isCheckOnLoginAllowed(User $user);
}