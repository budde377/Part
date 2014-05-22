<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/6/13
 * Time: 12:18 AM
 * To change this template use File | Settings | File Templates.
 */

class AJAXPageRegistrableImpl implements Registrable{

    private $container;
    private $currentUser;
    private $currentUserPrivileges;

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
        $this->currentUserPrivileges = $this->currentUser->getUserPrivileges();
        $jsonServer = new JSONServerImpl();
        $pageOrder = $this->container->getPageOrderInstance();
        $pageTranslator = new PageJSONObjectTranslatorImpl($pageOrder);
        $currentPage = $this->container->getCurrentPageStrategyInstance()->getCurrentPage();



        $deactivatePageFunction = new JSONFunctionImpl('deactivatePage',
            function ($id) use ($pageOrder) {
                if (!$this->currentUserPrivileges->hasSitePrivileges()) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }
                $response = null;
                if (($p = $pageOrder->getPage($id)) != null) {
                    $pageOrder->deactivatePage($p);
                    $response = new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_SUCCESS);
                } else {
                    $response = new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR,
                        JSONResponse::ERROR_CODE_PAGE_NOT_FOUND, "Siden blev ikke fundet");
                }
                return $response;
            }
            , array('page_id'));
        $jsonServer->registerJSONFunction($deactivatePageFunction);


        $changePageInfoFunction = new JSONFunctionImpl('changePageInfo',
            function ($pageId, $newPageId, $title, $template, $alias, $hidden) use ($pageOrder) {

                $page = $pageOrder->getPage($pageId);
                if ($page == null) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_PAGE_NOT_FOUND);
                }
                if (!$this->currentUserPrivileges->hasPagePrivileges($page)) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }

                if ($newPageId != $pageId && !$page->isValidId($newPageId)) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_PAGE_ID);
                }
                if (!$page->isValidAlias($alias)) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_PAGE_ALIAS);
                }
                if ($alias != $page->getAlias()) $page->setAlias($alias);
                if ($newPageId != $page->getID()) $page->setID($newPageId);
                if ($title != $page->getTitle()) $page->setTitle($title);
                if ($template != $page->getTemplate()) $page->setTemplate($template);
                if ($page->isHidden() && !$hidden) $page->show();
                if (!$page->isHidden() && $hidden) $page->hide();

                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_SUCCESS);
            }
            , array('page_id', 'new_page_id', 'title', 'template', 'alias', 'hidden'));

        $jsonServer->registerJSONFunction($changePageInfoFunction);

        $deletePageFunction = new JSONFunctionImpl('deletePage',
            function ($page_id) use ($pageOrder, $currentPage) {
                if (!$this->currentUserPrivileges->hasSitePrivileges()) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }
                if (($p = $pageOrder->getPage($page_id)) == null) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_PAGE_NOT_FOUND);
                }
                if ($currentPage == $p) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_CANT_DELETE_CURRENT_PAGE);
                }
                $pageOrder->deletePage($p);
                return new JSONResponseImpl();
            }
            , array('page_id'));

        $jsonServer->registerJSONFunction($deletePageFunction);

        $createPageFunction = new JSONFunctionImpl('createPage',
            function ($title) use ($pageOrder, $pageTranslator) {
                if (!$this->currentUserPrivileges->hasSitePrivileges()) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }
                if (strlen($title) == 0) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_PAGE_TITLE);
                }
                $id = strtolower($title);
                $id = $baseId = str_replace(' ', '_', $id);
                $id = $baseId = preg_replace('/[^a-z0-9\-_]/', '', $id);
                $i = 2;
                while (($p = $pageOrder->createPage($id)) === false) {
                    $id = $baseId . "_" . $i;
                    $i++;
                }
                $p->setTitle($title);
                $p->setTemplate('_main');
                $response = new JSONResponseImpl();
                $response->setPayload($pageTranslator->encode($p));
                return $response;
            }
            , array('title')
        );
        $jsonServer->registerJSONFunction($createPageFunction);



        return $jsonServer->evaluatePostInput()->getAsJSONString();
    }
}