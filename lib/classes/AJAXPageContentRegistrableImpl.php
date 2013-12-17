<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/6/13
 * Time: 12:17 AM
 * To change this template use File | Settings | File Templates.
 */

class AJAXPageContentRegistrableImpl implements Registrable{

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


        $jsonServer->registerJSONFunction(new JSONFunctionImpl('addPageContent', function ($page_id, $id, $content) use ($pageOrder) {
            $currentPage = $pageOrder->getPage($page_id);
            if (!$currentPage->exists() || !$currentPage->isEditable() || ($c = $currentPage->getContent($id)) == null) {
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_CANT_EDIT_PAGE);
            }
            $response = new JSONResponseImpl();
            $response->setPayload($c->addContent($content));
            return $response;
        }, array('page_id', 'id', 'content')));


        $jsonServer->registerJSONFunction(new JSONFunctionImpl('listPageContentRevisions', function($page_id, $id, $from, $to, $includeContent) use ($pageOrder){
            $currentPage = $pageOrder->getPage($page_id);
            $content = $currentPage->getContent($id);
            $contentList = $content->listContentHistory($from<=0?null:$from, $to<0?null:$to);
            if(!$includeContent){
                $contentList = array_map(function($ar){
                    return intval($ar['time']);
                }, $contentList);
            } else {
                $contentList = array_map(function($ar){
                    $ar['time'] = intval($ar['time']);
                    return $ar;
                }, $contentList);

            }
            $response = new JSONResponseImpl();
            $response->setPayload($contentList);
            return $response;
        }, array('page_id', 'id', 'from', 'to', 'content')));

        $jsonServer->registerJSONFunction(new JSONFunctionImpl('pageContentAtTime', function($page_id, $id, $time) use ($pageOrder){
            $currentPage = $pageOrder->getPage($page_id);
            $content = $currentPage->getContent($id);
            $response = new JSONResponseImpl();
            $response->setPayload($content->getContentAt($time));
            return $response;
        }, array('page_id', 'id', 'time')));


        return $jsonServer->evaluatePostInput()->getAsJSONString();
    }
}