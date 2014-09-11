<?php
namespace ChristianBudde\cbweb;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/3/14
 * Time: 9:54 PM
 */

class UserPrivilegesJSONObjectImpl extends JSONObjectImpl{


    function __construct(UserPrivileges $privileges)
    {
        parent::__construct("user_privileges");
        $this->setVariable('root_privileges', $privileges->hasRootPrivileges());
        $this->setVariable('site_privileges', $privileges->hasRootPrivileges());
        $this->setVariable('page_privileges', $privileges->listPagePrivileges());
    }
}