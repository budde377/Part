<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/5/13
 * Time: 11:59 PM
 * To change this template use File | Settings | File Templates.
 */

class AJAXUsersRegistrableImpl implements Registrable{

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
    public function callback($id)
    {

        if($this->currentUser == null){
            return (new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED))->getAsJSONString();
        }
        $jsonServer = new JSONServerImpl();
        $pageOrder = $this->container->getPageOrderInstance();
        $userLibrary = $this->container->getUserLibraryInstance();
        $userTranslator = new UserJSONObjectTranslatorImpl($userLibrary);

        $listUsersFunction = new JSONFunctionImpl('listUsers',
            function () use ($userLibrary, $userTranslator, $pageOrder) {
                $users = $userLibrary->getChildren($this->currentUser);
                $users[] = $this->currentUser;
                $newUsers = $pagePrivileges = array();

                /** @var $user User */
                foreach ($users as $user) {
                    $newUsers[] = $userTranslator->encode($user);
                    $privilege = $user->getUserPrivileges();
                    if (!$privilege->hasRootPrivileges() && !$privilege->hasSitePrivileges()) {
                        $pagePrivileges[$user->getUsername()] = array_map(function (Page $p) {
                            return $p->getID();
                        }, array_filter($pageOrder->listPages(), function (Page $p) use ($privilege) {
                            return $privilege->hasPagePrivileges($p);
                        }));
                    }
                }
                $payload = array();
                $payload['user_logged_in'] = $this->currentUser->getUsername();
                $payload['users'] = $newUsers;
                $payload['page_privileges'] = $pagePrivileges;
                $response = new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_SUCCESS);
                $response->setPayload($payload);
                return $response;
            }
        );
        $jsonServer->registerJSONFunction($listUsersFunction);
        return $jsonServer->evaluatePostInput()->getAsJSONString();
    }
}