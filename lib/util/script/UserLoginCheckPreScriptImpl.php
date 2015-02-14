<?php
namespace ChristianBudde\Part\util\script;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\Website;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/8/13
 * Time: 11:45 PM
 * To change this template use File | Settings | File Templates.
 */

class UserLoginCheckPreScriptImpl implements Script{
    private $backendContainer;
    public function __construct(BackendSingletonContainer $backendContainer){
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
        if($this->backendContainer->getUserLibraryInstance()->getUserLoggedIn() == null){
            return;
        }
        
        $this->backendContainer->getCacheControlInstance()->disableCache();

    }
}