<?php
require_once dirname(__FILE__) . '/../_interface/UserPrivileges.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 02/08/12
 * Time: 17:17
 */
class UserPrivilegesImpl implements UserPrivileges
{
    private $user;
    private $siteLibrary;
    private $connection;
    private $privileges = array();
    private $overrideMap = array();
    /** @var $addPrivilegePreparedStatement PDOStatement */
    private $addPrivilegePreparedStatement;
    private $isInitialized = false;
    /** @var $deletePreparedStatement PDOStatement */
    private $deleteRootPreparedStatement;
    /** @var $deletePagePreparedStatement PDOStatement */
    private $deletePagePreparedStatement;
    /** @var $deleteSitePreparedStatement PDOStatement */
    private $deleteSitePreparedStatement;
    /** @var $deleteAllPreparedStatement PDOStatement */
    private $deleteAllPreparedStatement;

    public function __construct(DB $database, User $user, SiteLibrary $siteLibrary)
    {
        $this->user = $user;
        $this->siteLibrary = $siteLibrary;
        $this->connection = $database->getConnection();

    }

    /**
     * Will add root privileges to user
     * @return bool FALSE on failure, else TRUE
     */
    public function addRootPrivilege()
    {
        return $this->addPrivilege(UserPrivileges::USER_PRIVILEGES_TYPE_ROOT);
    }

    /**
     * Will add site privileges to user
     * @param string $site Must be valid site title
     * @return bool FALSE on failure, else TRUE
     */
    public function addSitePrivilege($site)
    {
        return $this->addPrivilege(UserPrivileges::USER_PRIVILEGES_TYPE_SITE, $site);
    }

    /**
     * Will add Page privileges to user
     * @param string $site
     * @param string $page
     * @return bool FALSE on failure else TRUE
     */
    public function addPagePrivilege($site, $page)
    {
        return $this->addPrivilege(UserPrivileges::USER_PRIVILEGES_TYPE_PAGE, $site, $page);
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

        if ($site == null && $page == null) {
            if (isset($this->overrideMap[UserPrivileges::USER_PRIVILEGES_TYPE_ROOT])) {
                if ($this->deleteRootPreparedStatement == null) {
                    $this->deleteRootPreparedStatement = $this->connection->prepare("DELETE FROM UserPrivilege
                    WHERE username = ? AND type=?");
                }
                $this->deleteRootPreparedStatement->execute(array($this->user->getUsername(),
                    UserPrivileges::USER_PRIVILEGES_TYPE_ROOT));

                unset($this->overrideMap[UserPrivileges::USER_PRIVILEGES_TYPE_ROOT]);
                $nevPrivileges = array();

                foreach ($this->privileges as $privilege) {
                    if ($privilege['type'] != UserPrivileges::USER_PRIVILEGES_TYPE_ROOT) {
                        $nevPrivileges[] = $privilege;
                    }
                }

                $this->privileges = $nevPrivileges;
                return true;
            }
        } else if ($page == null) {
            if (isset($this->overrideMap[UserPrivileges::USER_PRIVILEGES_TYPE_SITE][$site])) {

                if ($this->deleteSitePreparedStatement == null) {
                    $this->deleteSitePreparedStatement = $this->connection->prepare("DELETE FROM UserPrivilege
                    WHERE username = ?  AND site = ? AND type=?");
                }

                $this->deleteSitePreparedStatement->execute(array($this->user->getUsername(), $site,
                    UserPrivileges::USER_PRIVILEGES_TYPE_SITE));

                unset($this->overrideMap[UserPrivileges::USER_PRIVILEGES_TYPE_SITE][$site]);
                $nevPrivileges = array();

                foreach ($this->privileges as $privilege) {
                    if ($privilege['type'] != UserPrivileges::USER_PRIVILEGES_TYPE_SITE || $privilege['site'] != $site) {
                        $nevPrivileges[] = $privilege;
                    }
                }
                $this->privileges = $nevPrivileges;
                return true;
            }
        } else if ($site != null) {
            if (isset($this->overrideMap[UserPrivileges::USER_PRIVILEGES_TYPE_PAGE][$site][$page])) {

                if ($this->deletePagePreparedStatement == null) {
                    $this->deletePagePreparedStatement = $this->connection->prepare("DELETE FROM UserPrivilege
                    WHERE username = ?  AND site = ? AND page = ? AND type=?");
                }

                $this->deletePagePreparedStatement->execute(array($this->user->getUsername(), $site,$page,
                    UserPrivileges::USER_PRIVILEGES_TYPE_PAGE));

                unset($this->overrideMap[UserPrivileges::USER_PRIVILEGES_TYPE_PAGE][$site][$page]);
                $nevPrivileges = array();

                foreach ($this->privileges as $privilege) {
                    if ($privilege['type'] != UserPrivileges::USER_PRIVILEGES_TYPE_PAGE || $privilege['site'] != $site
                        || $privilege['page'] != $page
                    ) {
                        $nevPrivileges[] = $privilege;
                    }
                }
                $this->privileges = $nevPrivileges;
                return true;
            }
        }
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
        $this->initializePrivileges();
        if ($site == null && $page == null) {
            return isset($this->overrideMap[UserPrivileges::USER_PRIVILEGES_TYPE_ROOT]);
        } else if ($page == null) {
            return isset($this->overrideMap[UserPrivileges::USER_PRIVILEGES_TYPE_SITE][$site]) || $this->isPrivileged();
        } else if ($site != null) {
            return isset($this->overrideMap[UserPrivileges::USER_PRIVILEGES_TYPE_PAGE][$site][$page]) || $this->isPrivileged($site);
        }
        return false;
    }

    private function addPrivilege($type, $site = null, $page = null, $insertIntoDB = true)
    {


        if (($type == UserPrivileges::USER_PRIVILEGES_TYPE_SITE || $type == UserPrivileges::USER_PRIVILEGES_TYPE_PAGE)) {
            $s = $this->siteLibrary->getSite($site);
            if ($s == null) {
                return false;
            }

            if ($type == UserPrivileges::USER_PRIVILEGES_TYPE_PAGE) {
                $pageOrder = $s->getPageOrder();
                if ($pageOrder->getPage($page) == null || isset($this->overrideMap[$type][$site][$page])) {
                    return false;
                }
                $this->overrideMap[$type][$site][$page] = 1;
            } else {
                if (isset($this->overrideMap[$type][$site])) {
                    return false;
                }
                $this->overrideMap[$type][$site] = 1;
            }
        } else if ($type == UserPrivileges::USER_PRIVILEGES_TYPE_ROOT) {
            if (isset($this->overrideMap[$type])) {
                return false;
            }
            $this->overrideMap[$type] = 1;
        }
        if ($insertIntoDB) {
            if ($this->addPrivilegePreparedStatement == null) {
                $this->addPrivilegePreparedStatement = $this->connection->prepare("INSERT INTO UserPrivilege (page,site,type,username) VALUES (?,?,?,?)");
            }

            $this->addPrivilegePreparedStatement->execute(array($page, $site, $type, $this->user->getUsername()));
        }

        $this->privileges[] = array('type' => $type, 'site' => $site, 'page' => $page);


        return true;
    }

    private function initializePrivileges()
    {
        if (!$this->isInitialized) {
            $this->isInitialized = true;
            $statement = $this->connection->prepare("SELECT * FROM UserPrivilege WHERE username = ?");
            $statement->execute(array($this->user->getUsername()));
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $this->addPrivilege($row['type'], $row['site'], $row['page'], false);
                switch ($row['type']) {
                    case UserPrivileges::USER_PRIVILEGES_TYPE_ROOT:
                        $this->overrideMap[$row['type']] = 1;
                        break;
                    case UserPrivileges::USER_PRIVILEGES_TYPE_SITE:
                        $this->overrideMap[$row['type']][$row['site']] = 1;
                        break;
                    case UserPrivileges::USER_PRIVILEGES_TYPE_PAGE:
                        $this->overrideMap[$row['type']][$row['site']][$row['page']] = 1;
                        break;

                }
            }
        }
    }

    /**
     * Will revoke all privileges
     * @return void
     */
    public function revokeAllPrivileges()
    {
        if($this->deleteAllPreparedStatement == null){
            $this->deleteAllPreparedStatement = $this->connection->prepare("DELETE FROM UserPrivilege WHERE username=?");
        }

        $this->deleteAllPreparedStatement->execute(array($this->user->getUsername()));
        $this->overrideMap = array();
        $this->privileges = array();

    }


    /**
     * Will return an array with all privileges.
     * Every entrance will have another array with entrances type, site, page
     * @param String $mode
     * @return array
     */
    public function listPrivileges($mode = UserPrivileges::LIST_MODE_LIST_ALL)
    {
        $this->initializePrivileges();
        $returnArray = array();

        switch ($mode) {
            case UserPrivileges::LIST_MODE_LIST_PAGE:
            case UserPrivileges::LIST_MODE_LIST_ROOT:
            case UserPrivileges::LIST_MODE_LIST_SITE:
                foreach ($this->privileges as $privilege) {
                    if ($privilege['type'] == $mode) {
                        $returnArray[] = $privilege;
                    }
                }
                break;
            case UserPrivileges::LIST_MODE_LIST_ALL:
                $returnArray = $this->privileges;
                break;
        }

        return $returnArray;

    }
}
