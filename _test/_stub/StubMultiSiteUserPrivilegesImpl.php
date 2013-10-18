<?php
require_once dirname(__FILE__) . '/../../_interface/MultiSiteUserPrivileges.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 06/08/12
 * Time: 22:34
 */
class StubMultiSiteUserPrivilegesImpl implements MultiSiteUserPrivileges
{
    private $privileges;
    private $isPrivileged;

    /**
     * Will add root privileges to user
     * @return bool FALSE on failure, else TRUE
     */
    public function addRootPrivilege()
    {
        return false;
    }

    /**
     * Will add site privileges to user
     * @param string $site Must be valid site title
     * @return bool FALSE on failure, else TRUE
     */
    public function addSitePrivilege($site)
    {
        return false;
    }

    /**
     * Will add Page privileges to user
     * @param string $site
     * @param string $page
     * @return bool FALSE on failure else TRUE
     */
    public function addPagePrivilege($site, $page)
    {
        return false;
    }

    /**
     * Will revoke the privileges specified
     * If Root privilege should be revoked, $site and $page must be null,
     * If Site privilege should be revoked, $site should be not null and $page null
     * If Page privilege should be revoked, $site and $page should be not null
     * @param string | null $site
     * @param string | null $page
     * @return bool FALSE on failure as not found, else TRUE
     */
    public function revokePrivilege($site = null, $page = null)
    {
        return false;
    }


    /**
     * Will test if privileged.
     * If test for Root privilege, test with site and page equal null
     * If test for site, test with page equal null and site not null.
     * If test for page privilege, test with page and site not null
     * @param null | string $site Site title
     * @param null | string $page Page title within site
     * @return bool FALSE on not privileged else TRUE
     */
    public function isPrivileged($site = null, $page = null)
    {
        if ($site == null && $page == null) {
            return isset($this->isPrivileged[MultiSiteUserPrivileges::USER_PRIVILEGES_TYPE_ROOT]);
        } else if ($page == null) {
            return isset($this->isPrivileged[MultiSiteUserPrivileges::USER_PRIVILEGES_TYPE_SITE][$site]) || $this->isPrivileged();
        } else if ($site != null) {
            return isset($this->isPrivileged[MultiSiteUserPrivileges::USER_PRIVILEGES_TYPE_PAGE][$site][$page]) || $this->isPrivileged($site);
        }
        return false;
    }

    /**
     * Will revoke all privileges
     * @return void
     */
    public function revokeAllPrivileges()
    {

    }

    public function setPrivileges(array $privileges)
    {
        $this->privileges = $privileges;
    }

    public function setIsPrivileged($site = null, $page = null)
    {
        if ($site == null && $page == null) {
            $this->isPrivileged[MultiSiteUserPrivileges::USER_PRIVILEGES_TYPE_ROOT] = 1;
        } else if ($page == null) {
            $this->isPrivileged[MultiSiteUserPrivileges::USER_PRIVILEGES_TYPE_SITE][$site] = 1;
        } else if ($site != null) {
            $this->isPrivileged[MultiSiteUserPrivileges::USER_PRIVILEGES_TYPE_PAGE][$site][$page] = 1;
        }

    }

    /**
     * Will return an array with all privileges.
     * Every entrance will have another array with entrances type, site, page
     * @param String $mode
     * @return array
     */
    public function listPrivileges($mode = MultiSiteUserPrivileges::LIST_MODE_LIST_ALL)
    {
        $returnArray = array();

        switch ($mode) {
            case MultiSiteUserPrivileges::LIST_MODE_LIST_PAGE:
            case MultiSiteUserPrivileges::LIST_MODE_LIST_ROOT:
            case MultiSiteUserPrivileges::LIST_MODE_LIST_SITE:
                foreach ($this->privileges as $privilege) {
                    if ($privilege['type'] = $mode) {
                        $returnArray[] = $privilege;
                    }
                }
                break;
            case MultiSiteUserPrivileges::LIST_MODE_LIST_ALL:
                $returnArray = $this->privileges;
                break;
        }

        return $returnArray;
    }
}
