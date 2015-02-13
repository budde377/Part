<?php
namespace ChristianBudde\Part\model\user;
use ChristianBudde\Part\controller\json\JSONObjectSerializable;
use ChristianBudde\Part\model\page\Page;
use ChristianBudde\Part\model\page\PageOrder;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 22:09
 */
interface UserPrivileges extends JSONObjectSerializable
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
     * Will return an array of strings containing the sites that are under the users control.
     * If the user has site or root privileges an empty array is returned.
     * If the user has no privileges an empty array is returned.
     *
     * @param PageOrder $pageOrder If order is given it will return array containing instances from the PageOrder
     * @return array
     */
    public function listPagePrivileges(PageOrder $pageOrder = null);

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

    /**
     * @return User
     */
    public function getUser();
}
