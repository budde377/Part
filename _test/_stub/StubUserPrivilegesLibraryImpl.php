<?php
require_once dirname(__FILE__) . '/../../_interface/UserPrivilegesLibrary.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 06/08/12
 * Time: 22:28
 */
class StubUserPrivilegesLibraryImpl implements UserPrivilegesLibrary
{
    public $privileges = array();

    /**
     * @param User $user
     * @return UserPrivileges
     */
    public function getPrivileges(User $user)
    {
        return $this->privileges[$user->getUsername()];
    }


    public function setPrivileges(array $privileges){
        $this->privileges = $privileges;
    }
}
