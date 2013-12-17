<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 23/04/13
 * Time: 22:47
 * To change this template use File | Settings | File Templates.
 */

class StubUserPrivilegesImpl implements  UserPrivileges{
    private $root, $site, $page;

    public function __construct($root, $site, $page){
        $this->root = $root;
        $this->site = $site;
        $this->page = $page;
    }

    /**
     * Will add root privileges
     * @return void
     */
    public function addRootPrivileges()
    {
        // TODO: Implement addRootPrivileges() method.
    }

    /**
     * Will add Site privileges
     * @return void
     */
    public function addSitePrivileges()
    {
        // TODO: Implement addSitePrivileges() method.
    }

    /**
     * Will add privileges to given page
     * @param Page $page
     * @return void
     */
    public function addPagePrivileges(Page $page)
    {
        // TODO: Implement addPagePrivileges() method.
    }

    /**
     * @return bool Return TRUE if has privilege else FALSE
     */
    public function hasRootPrivileges()
    {
        return $this->root;
    }

    /**
     * @return bool Return TRUE if has privilege else FALSE
     */
    public function hasSitePrivileges()
    {
        return $this->site;
    }

    /**
     * @param Page $page
     * @return bool Return TRUE if has privilege else FALSE
     */
    public function hasPagePrivileges(Page $page)
    {
        return $this->page;
    }

    /**
     * Will revoke Root privileges
     * @return void
     */
    public function revokeRootPrivileges()
    {
        // TODO: Implement revokeRootPrivileges() method.
    }

    /**
     * Will revoke Site privileges
     * @return void
     */
    public function revokeSitePrivileges()
    {
        // TODO: Implement revokeSitePrivileges() method.
    }

    /**
     * Will revoke privileges from given Page
     * @param Page $page
     * @return void
     */
    public function revokePagePrivileges(Page $page)
    {
        // TODO: Implement revokePagePrivileges() method.
    }

    /**
     * This will revoke all privileges
     * @return void
     */
    public function revokeAllPrivileges()
    {
        // TODO: Implement revokeAllPrivileges() method.
    }
}