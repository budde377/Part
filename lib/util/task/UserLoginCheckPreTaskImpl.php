<?php
namespace ChristianBudde\Part\util\task;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\Website;

/**
 * User: budde
 * Date: 9/8/13
 * Time: 11:45 PM
 */

class UserLoginCheckPreTaskImpl implements Task{
    private $backendContainer;
    public function __construct(BackendSingletonContainer $backendContainer){
        $this->backendContainer = $backendContainer;
    }


    public function run()
    {

        if($this->backendContainer->getUserLibraryInstance()->getUserLoggedIn() == null){
            return;
        }
        
        $this->backendContainer->getCacheControlInstance()->disableCache();

    }
}