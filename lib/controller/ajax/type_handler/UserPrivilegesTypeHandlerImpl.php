<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/4/15
 * Time: 12:56 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\page\Page;
use ChristianBudde\Part\model\user\UserPrivileges;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class UserPrivilegesTypeHandlerImpl extends GenericObjectTypeHandlerImpl
{

    private $container;

    use TypeHandlerTrait;

    function __construct(BackendSingletonContainer $container, UserPrivileges $privileges)
    {
        $this->container = $container;
        parent::__construct($privileges);

        $this->addGetInstanceFunction("UserPrivileges");
        $this->addTypePreCallFunction('UserPrivileges', function ($type, $instance, $functionName, &$arguments) {
            if ($functionName != 'addPagePrivileges' && $functionName != 'hasPagePrivileges' && $functionName != 'revokePagePrivileges') {
                return;
            }
            if (!isset($arguments[0])) {
                return;
            }
            if ($arguments[0] instanceof Page) {
                return;
            }
            $arguments[0] = $this->container->getPageOrderInstance()->getPage($arguments[0]);
        });
        $this->addTypeAuthFunction('UserPrivileges', function ($type, UserPrivileges $instance, $functionName) {
            if (in_array($functionName, [
                'hasRootPrivileges',
                'hasSitePrivileges',
                'hasPagePrivileges',
                'listPagePrivileges',
                'getUser'])) {
                return true;
            }
            $currentUser = $this->container->getUserLibraryInstance()->getUserLoggedIn();
            $user = $instance->getUser();
            if (!$this->isChildAuthFunction($user, $currentUser, $this->container->getUserLibraryInstance())) {
                return false;
            }
            return true;
        });
    }


}