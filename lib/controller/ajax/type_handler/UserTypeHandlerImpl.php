<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:09 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\json\Response;
use ChristianBudde\Part\controller\json\ResponseImpl;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class UserTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;

    use TypeHandlerTrait;

    function __construct(BackendSingletonContainer $container, User $user)
    {
        $this->container = $container;

        parent::__construct($user, 'User');
        $this->whitelistFunction('User',
            "getUsername",
            "getMail",
            "getLastLogin",
            "getParent",
            "getUserPrivileges",
            "getUniqueId",
            "setMail",
            "setUsername",
            "setPassword",
            "logout",
            "isValidMail",
            "isValidUsername",
            "isValidPassword",
            "delete",
            "getInstance");
        $this->addGetInstanceFunction('User');
        /** @noinspection PhpUnusedParameterInspection */
        $this->addTypeAuthFunction('User', function ($type, $instance, $functionName, $args) {
            return substr($functionName, 0, 3) != "set" || $this->container->getUserLibraryInstance()->getUserLoggedIn() === $instance;
        });
        /** @noinspection PhpUnusedParameterInspection */
        $this->addFunctionAuthFunction('User', 'delete', function ($type, $instance) {
            $userLibrary = $this->container->getUserLibraryInstance();
            return $this->isChildAuthFunction($instance, $userLibrary->getUserLoggedIn(), $userLibrary);
        });
        $this->addFunction('User', 'setPassword', function (User $user, $oldPassword, $newPassword) {
            if (!$user->verifyLogin($oldPassword)) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_WRONG_PASSWORD);
            }
            if (!$user->setPassword($newPassword)) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_PASSWORD);
            }
            return new ResponseImpl();
        });
    }


}