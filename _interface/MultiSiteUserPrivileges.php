<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 02/08/12
 * Time: 17:11
 */
interface MultiSiteUserPrivileges
{
    const USER_PRIVILEGES_TYPE_ROOT = 'root';
    const USER_PRIVILEGES_TYPE_SITE = 'site';
    const USER_PRIVILEGES_TYPE_PAGE = 'page';

    const LIST_MODE_LIST_ALL = 'all';
    const LIST_MODE_LIST_ROOT = 'root';
    const LIST_MODE_LIST_SITE = 'site';
    const LIST_MODE_LIST_PAGE = 'page';


    /**
     * @abstract
     * Will add root privileges to user
     * @return bool FALSE on failure, else TRUE
     */
    public function addRootPrivilege();

    /**
     * @abstract
     * Will add site privileges to user
     * @param string $site Must be valid site title
     * @return bool FALSE on failure, else TRUE
     */
    public function addSitePrivilege($site);

    /**
     * @abstract
     * Will add Page privileges to user
     * @param string $site
     * @param string $page
     * @return bool FALSE on failure else TRUE
     */
    public function addPagePrivilege($site, $page);

    /**
     * @abstract
     * Will revoke the privileges specified
     * If Root privilege should be revoked, $site and $page must be null,
     * If Site privilege should be revoked, $site should be not null and $page null
     * If Page privilege should be revoked, $site and $page should be not null
     * @param string | null $site
     * @param string | null $page
     * @return bool FALSE on failure as not found, else TRUE
     */
    public function revokePrivilege($site = null, $page = null);

    /**
     * @abstract
     * Will return an array with all privileges.
     * Every entrance will have another array with entrances type, site, page
     * @param String $mode
     * @return array
     */
    public function listPrivileges($mode = MultiSiteUserPrivileges::LIST_MODE_LIST_ALL);


    /**
     * @abstract
     * Will test if privileged.
     * If test for Root privilege, test with site and page equal null
     * If test for site, test with page equal null and site not null.
     * If test for page privilege, test with page and site not null
     * @param null | string $site Site title
     * @param null | string $page Page title within site
     * @return bool FALSE on not privileged else TRUE
     */
    public function isPrivileged($site = null, $page = null);


    /**
     * @abstract
     * Will revoke all privileges
     * @return void
     */
    public function revokeAllPrivileges();


}
