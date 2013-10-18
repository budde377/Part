<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 05/08/12
 * Time: 18:16
 */
interface MultiSiteUserPrivilegesLibrary
{
    /**
     * @abstract
     * @param User $user
     * @return MultiSiteUserPrivileges
     */
    public function getPrivileges(User $user);

}
