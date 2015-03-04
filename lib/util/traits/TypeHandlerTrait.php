<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 10:48 PM
 */

namespace ChristianBudde\Part\util\traits;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\model\user\UserLibrary;

trait TypeHandlerTrait {

    private function wrapFunction(callable $f){
        return function ()use ($f){
            return call_user_func_array($f, func_get_args());
        };
    }



    private function currentUserSitePrivilegesAuthFunction(BackendSingletonContainer $container){
        return function () use ($container) {
            $user = $container->getUserLibraryInstance()->getUserLoggedIn();
            if ($user == null) {
                return false;
            }
            return $user->getUserPrivileges()->hasSitePrivileges();
        };
    }


    private function currentUserLoggedInAuthFunction(BackendSingletonContainer $container){
        return function () use ($container) {
            return $container->getUserLibraryInstance()->getUserLoggedIn() != null;
        };
    }



    private function isChildAuthFunction($child, $parent, $userLibrary)
    {

        if(!($child instanceof User)){
            return false;
        }
        if(!($parent instanceof User)){
            return false;
        }

        if(!($userLibrary instanceof UserLibrary)){
            return false;
        }


        return in_array($child, $userLibrary->getChildren($parent));
    }
}