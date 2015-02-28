<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 11:21 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\updater\Updater;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

//TODO test this

class UpdaterTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    use TypeHandlerTrait;

    private $userLibrary;

    function __construct(BackendSingletonContainer $container, Updater $updater)
    {
        parent::__construct($updater, 'Updater');
        $this->userLibrary = $container->getUserLibraryInstance();
        $this->addFunctionAuthFunction('Updater', 'update', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction('Updater', 'checkForUpdates', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction('Updater', 'allowCheckOnLogin', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction('Updater', 'disallowCheckOnLogin', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction('Updater', 'isCheckOnLoginAllowed', $this->currentUserSitePrivilegesAuthFunction($container));

        $this->addFunction('Updater', 'allowCheckOnLogin', function(Updater $instance){
            $user = $this->userLibrary->getUserLoggedIn();
            $instance->allowCheckOnLogin($user);
        });

        $this->addFunction('Updater', 'disallowCheckOnLogin', function(Updater $instance){
            $user = $this->userLibrary->getUserLoggedIn();
            $instance->disallowCheckOnLogin($user);
        });

        $this->addFunction('Updater', 'isCheckOnLoginAllowed', function(Updater $instance){
            $user = $this->userLibrary->getUserLoggedIn();
            return $instance->isCheckOnLoginAllowed($user);
        });
    }
}