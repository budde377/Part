<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 22:09
 */
interface UserPrivileges
{


    /**
     * Will add root privileges
     * @return void
     */
    public function addRootPrivileges();

    /**
     * Will add Site privileges
     * @return void
     */
    public function addSitePrivileges();

    /**
     * Will add privileges to given page
     * @param Page $page
     * @return void
     */
    public function addPagePrivileges(Page $page);

    /**
     * @return bool Return TRUE if has privilege else FALSE
     */
    public function hasRootPrivileges();

    /**
     * @return bool Return TRUE if has privilege else FALSE
     */
    public function hasSitePrivileges();

    /**
     * @param Page $page
     * @return bool Return TRUE if has privilege else FALSE
     */
    public function hasPagePrivileges(Page $page);

    /**
     * Will revoke Root privileges
     * @return void
     */
    public function revokeRootPrivileges();

    /**
     * Will revoke Site privileges
     * @return void
     */
    public function revokeSitePrivileges();

    /**
     * Will revoke privileges from given Page
     * @param Page $page
     * @return void
     */
    public function revokePagePrivileges(Page $page);

    /**
     * This will revoke all privileges
     * @return void
     */
    public function revokeAllPrivileges();
}
