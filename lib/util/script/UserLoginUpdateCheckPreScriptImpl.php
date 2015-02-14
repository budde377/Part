<?php
namespace ChristianBudde\Part\util\script;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\Website;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/20/14
 * Time: 8:41 PM
 */

class UserLoginUpdateCheckPreScriptImpl implements  Script{

    /** @var  BackendSingletonContainer */
    private  $backendContainer;

    function __construct(BackendSingletonContainer $backendContainer)
    {
        $this->backendContainer = $backendContainer;
    }


    /**
     * This function runs the script
     * @param $name string
     * @param $args array | null
     */
    public function run($name, $args)
    {
        if($name != Website::WEBSITE_SCRIPT_TYPE_PRESCRIPT){
            return;
        }

        if(($user = $this->backendContainer->getUserLibraryInstance()->getUserLoggedIn()) == null){
            return;
        }

        if(!$user->getUserPrivileges()->hasSitePrivileges()){
            return;
        }

        $updater = $this->backendContainer->getUpdater();

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