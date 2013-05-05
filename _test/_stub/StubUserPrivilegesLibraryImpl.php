<?php
require_once dirname(__FILE__).'/../../_interface/UserPrivilegesLibrary.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 23/04/13
 * Time: 22:47
 * To change this template use File | Settings | File Templates.
 */

class StubUserPrivilegesLibraryImpl implements  UserPrivilegesLibrary{

    private $userPrivileges;

    /**
     * This will keep and reuse instances of UserPrivilege
     * @param User $user
     * @return UserPrivileges
     */
    public function getUserPrivileges(User $user)
    {
        return $this->userPrivileges;
    }

    public function setUserPrivileges(UserPrivileges $userPrivileges)
    {
        $this->userPrivileges = $userPrivileges;
    }


}