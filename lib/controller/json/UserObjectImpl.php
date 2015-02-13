<?php
namespace ChristianBudde\Part\controller\json;

use ChristianBudde\Part\model\user\User;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 24/01/13
 * Time: 09:54
 * To change this template use File | Settings | File Templates.
 */
class UserObjectImpl extends ObjectImpl
{
    public function __construct(User $user){
        parent::__construct('user');
        $this->setVariable('username',$user->getUsername());
        $this->setVariable('mail',$user->getMail());
        $this->setVariable('parent',$user->getParent());
        $this->setVariable('privileges',$user->getUserPrivileges());
        $this->setVariable('last-login',$user->getLastLogin());
    }
}
