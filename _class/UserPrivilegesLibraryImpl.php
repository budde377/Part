<?php
require_once dirname(__FILE__).'/../_interface/UserPrivilegesLibrary.php';
require_once dirname(__FILE__).'/UserPrivilegesImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 19/01/13
 * Time: 13:20
 */
class UserPrivilegesLibraryImpl implements UserPrivilegesLibrary
{
    private $db;

    private $privileges = array();
    private $users = array();

    function __construct(DB $db)
    {
        $this->db = $db;
    }


    /**
     * This will keep and reuse instances of UserPrivilege
     * @param User $user
     * @return UserPrivileges
     */
    public function getUserPrivileges(User $user)
    {
        if (($privilege = $this->getCachePrivilege($user)) == null) {
            $privilege = new UserPrivilegesImpl($user, $this->db);
            $this->users[] = $user;
            $this->privileges[] = $privilege;
        }
        return $privilege;    }


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
