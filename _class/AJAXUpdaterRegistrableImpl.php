<?php
require_once dirname(__FILE__) . '/JSONServerImpl.php';
require_once dirname(__FILE__) . '/../_interface/Registrable.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/6/13
 * Time: 2:32 PM
 * To change this template use File | Settings | File Templates.
 */

class AJAXUpdaterRegistrableImpl implements Registrable{

    private $container;
    private $currentUser;


    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->currentUser = $this->container->getUserLibraryInstance()->getUserLoggedIn();
    }


    /**
     * @param $id string
     * @return string | null Will return string if id is found, else null
     */
    public function callback($id) {
        if(!$this->container->getConfigInstance()->isUpdaterEnabled()){
            return null;
        }

        if($this->currentUser==null || !$this->currentUser->getUserPrivileges()->hasSitePrivileges()){
            return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
        }
        $jsonServer = new JSONServerImpl();
        $updater = $this->container->getUpdater();
        $pageOrder = $this->container->getPageOrderInstance();

        $jsonServer->registerJSONFunction(new JSONFunctionImpl('checkForUpdates',function() use ($updater){
            $response = new JSONResponseImpl();
            $response->setPayload($updater->checkForUpdates());
            return $response;
        }));


        $jsonServer->registerJSONFunction(new JSONFunctionImpl('update',function() use ($updater, $pageOrder){
            $version = $updater->getVersion();
            $updater->update();
            exec("make update");
            if($version != $updater->getVersion()){
                foreach($pageOrder->listPages() as $page){
                    /** @var $page Page */
                    $page->modify();
                }
            }

            return new JSONResponseImpl();
        }));


        return $jsonServer->evaluatePostInput()->getAsJSONString();

    }
}