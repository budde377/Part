<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 05/08/12
 * Time: 18:16
 */
interface UserPrivilegesLibrary
{
    /**
     * @abstract
     * @param User $user
     * @return UserPrivileges
     */
    public function getPrivileges(User $user);

}
