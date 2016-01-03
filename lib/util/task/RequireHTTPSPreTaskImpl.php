<?php
namespace ChristianBudde\Part\util\task;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\util\helper\HTTPHeaderHelper;
use ChristianBudde\Part\Website;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/13/14
 * Time: 5:05 PM
 */

class RequireHTTPSPreTaskImpl implements Task{
    private $backendContainer;
    public function __construct(BackendSingletonContainer $backendContainer){
        $this->backendContainer = $backendContainer;
    }

    public function run()
    {

        if($this->backendContainer->getConfigInstance()->getDomain() !== $_SERVER['HTTP_HOST']){
            return;
        }

        if(!$this->isSecure()){
            HTTPHeaderHelper::redirectToLocation("https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        }

    }

    private function isSecure() {
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }
}