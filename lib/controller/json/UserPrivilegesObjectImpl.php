<?php
namespace ChristianBudde\Part\controller\json;

use ChristianBudde\Part\model\user\UserPrivileges;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/3/14
 * Time: 9:54 PM
 */

class UserPrivilegesObjectImpl extends ObjectImpl
{


    function __construct(UserPrivileges $privileges)
    {
        parent::__construct("user_privileges");
        $this->setVariable('root_privileges', $privileges->hasRootPrivileges());
        $this->setVariable('site_privileges', $privileges->hasSitePrivileges());
        $this->setVariable('page_privileges', $privileges->listPagePrivileges());
    }
}