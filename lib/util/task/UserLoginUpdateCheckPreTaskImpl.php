<?php
namespace ChristianBudde\Part\util\task;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\Website;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/20/14
 * Time: 8:41 PM
 */

class UserLoginUpdateCheckPreTaskImpl implements  Task{

    /** @var  BackendSingletonContainer */
    private  $backendContainer;

    function __construct(BackendSingletonContainer $backendContainer)
    {
        $this->backendContainer = $backendContainer;
    }


    public function run()
    {

        if(($user = $this->backendContainer->getUserLibraryInstance()->getUserLoggedIn()) == null){
            return;
        }

        $this->backendContainer->getCacheControlInstance()->disableCache();

        if(!$user->getUserPrivileges()->hasSitePrivileges()){
            return;
        }

        $updater = $this->backendContainer->getUpdaterInstance();

        if(!$updater->isCheckOnLoginAllowed($user)){
            return;
        }

        $i = $user->getLastLogin();
        if($updater->lastChecked() >= $i){
            return;
        }
        $updater->checkForUpdates();
    }
}