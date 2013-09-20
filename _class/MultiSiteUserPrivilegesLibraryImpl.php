<?php
require_once dirname(__FILE__) . '/../_interface/MultiSiteUserPrivilegesLibrary.php';
require_once dirname(__FILE__) . '/MultiSiteUserPrivilegesImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 05/08/12
 * Time: 19:59
 */
class MultiSiteUserPrivilegesLibraryImpl implements MultiSiteUserPrivilegesLibrary
{
    private $db;
    private $siteLibrary;

    private $privileges = array();
    private $users = array();


    public function __construct(DB $db, SiteLibrary $siteLibrary)
    {
        $this->db = $db;
        $this->siteLibrary = $siteLibrary;

    }


    /**
     * @param User $user
     * @return MultiSiteUserPrivileges
     */
    public function getPrivileges(User $user)
    {
        if (($privilege = $this->getCachePrivilege($user)) == null) {
            $privilege = new MultiSiteUserPrivilegesImpl($this->db,$user,$this->siteLibrary);
            $this->users[] = $user;
            $this->privileges[] = $privilege;
        }
        return $privilege;
    }

    /**
     * @param $user
     * @return null | MultiSiteUserPrivileges
     */
    private function getCachePrivilege($user)
    {
        foreach($this->users as $key=>$u){
            if($u === $user){
                return $this->privileges[$key];
            }
        }
        return null;
    }

}
