<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 24/01/13
 * Time: 09:54
 * To change this template use File | Settings | File Templates.
 */
class UserJSONObjectImpl extends JSONObjectImpl
{
    public function __construct($username,$email,$privileges, $lastLogin,$parent = ''){
        parent::__construct('user');
        $this->setVariable('username',$username);
        $this->setVariable('mail',$email);
        $this->setVariable('parent',$parent);
        $this->setVariable('privileges',$privileges);
        $this->setVariable('last-login',$lastLogin);
    }
}
