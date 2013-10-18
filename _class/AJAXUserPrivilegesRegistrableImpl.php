<?php
require_once dirname(__FILE__) . '/../_interface/Registrable.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/6/13
 * Time: 12:08 AM
 * To change this template use File | Settings | File Templates.
 */

class AJAXUserPrivilegesRegistrableImpl implements Registrable{

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


        $addUserPagePrivilege = new JSONFunctionImpl('addUserPagePrivilege',
            function ($username, $page_id) use ($userLibrary, $pageOrder) {
                $currentUserPrivileges = $userLibrary->getUserLoggedIn()->getUserPrivileges();
                if (!$currentUserPrivileges->hasRootPrivileges() && !$currentUserPrivileges->hasSitePrivileges()) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }
                $user = $userLibrary->getUser($username);
                if ($user == null) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_USER_NOT_FOUND);
                }
                $page = $pageOrder->getPage($page_id);
                if ($page == null) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_PAGE_NOT_FOUND);
                }
                $privileges = $user->getUserPrivileges();
                if ($privileges->hasRootPrivileges() || $privileges->hasSitePrivileges() || $privileges->hasPagePrivileges($page)) {
                    return new JSONResponseImpl();
                }
                $parent = $user->getParent();
                $parentFound = false;
                while ($parent != null && !$parentFound) {
                    $parentFound = $parent == $userLibrary->getUserLoggedIn()->getUsername();
                    $parent = $userLibrary->getUser($parent)->getParent();
                }
                if (!$parentFound) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }
                $privileges->addPagePrivileges($page);
                return new JSONResponseImpl();
            }, array('username', 'page_id'));
        $jsonServer->registerJSONFunction($addUserPagePrivilege);


        $revokeUserPagePrivilege = new JSONFunctionImpl('revokeUserPagePrivilege',
            function ($username, $page_id) use ($userLibrary, $pageOrder) {
                $user = $userLibrary->getUser($username);
                if ($user == null) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_USER_NOT_FOUND);
                }
                $page = $pageOrder->getPage($page_id);
                if ($page == null) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_PAGE_NOT_FOUND);
                }
                $privileges = $user->getUserPrivileges();
                if ($privileges->hasRootPrivileges() || $privileges->hasSitePrivileges() || !$privileges->hasPagePrivileges($page)) {
                    return new JSONResponseImpl();
                }
                $parent = $user->getParent();
                $parentFound = false;
                while ($parent != null && !$parentFound) {
                    $parentFound = $parent == $userLibrary->getUserLoggedIn()->getUsername();
                    $parent = $userLibrary->getUser($parent)->getParent();
                }
                if (!$parentFound) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }
                $privileges->revokePagePrivileges($page);
                return new JSONResponseImpl();
            }, array('username', 'page_id'));
        $jsonServer->registerJSONFunction($revokeUserPagePrivilege);


        return $jsonServer->evaluatePostInput()->getAsJSONString();
    }
}