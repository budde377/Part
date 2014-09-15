<?php
namespace ChristianBudde\cbweb\util\script;
use ChristianBudde\cbweb\BackendSingletonContainer;
use ChristianBudde\cbweb\Website;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/20/14
 * Time: 8:41 PM
 */

class UserLoginUpdateCheckPreScript implements  Script{

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
        $i = $user->getLastLogin();
        if($updater->lastChecked() >= $i){
            return;
        }
        $updater->checkForUpdates();
    }
}