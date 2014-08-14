<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/13/14
 * Time: 5:05 PM
 */

class RequireHTTPSPreScript implements Script{
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