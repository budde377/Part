<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:09 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\user\User;

class UserTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;
    private $user;

    function __construct(BackendSingletonContainer $container, User $user)
    {
        $this->container = $container;
        $this->user = $user;
    }


}