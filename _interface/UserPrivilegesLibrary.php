<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 19/01/13
 * Time: 13:18
 */
interface UserPrivilegesLibrary
{

    /**
     * This will keep and reuse instances of UserPrivilege
     * @param User $user
     * @return UserPrivileges
     */
    public function getUserPrivileges(User $user);

}
