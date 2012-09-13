<?php
require_once dirname(__FILE__) . '/../_interface/UserPrivilegesLibrary.php';
require_once dirname(__FILE__) . '/UserPrivilegesImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 05/08/12
 * Time: 19:59
 */
class UserPrivilegesLibraryImpl implements UserPrivilegesLibrary
{
    private $db;
    private $siteLibrary;

    private $privileges = array();
    private $users = array();

    private $array;

    public function __construct(DB $db, SiteLibrary $siteLibrary)
    {
        $this->db = $db;
        $this->siteLibrary = $siteLibrary;

    }


    /**
     * @param User $user
     * @return UserPrivileges
     */
    public function getPrivileges(User $user)
    {
        if (($privilege = $this->getCachePrivilege($user)) == null) {
            $privilege = new UserPrivilegesImpl($this->db,$user,$this->siteLibrary);
            $this->users[] = $user;
            $this->privileges[] = $privilege;
        }
        return $privilege;
    }

    /**
     * @param $user
     * @return null | UserPrivileges
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
