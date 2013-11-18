<?php
require_once dirname(__FILE__).'/../_interface/Registrable.php';
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/18/13
 * Time: 7:14 PM
 */

class AJAXSiteContentRegistrableImpl implements Registrable{

    private $container;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
    }



    /**
     * @param $id string
     * @return string | null Will return string if id is found, else null
     */
    public function callback($id)
    {
        $user = $this->container->getUserLibraryInstance()->getUserLoggedIn();
        if($user == null || !$user->getUserPrivileges()->hasSitePrivileges()){
            return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
        }

        $jsonServer = new JSONServerImpl();
        $site = $this->container->getSiteInstance();

        $jsonServer->registerJSONFunction(new JSONFunctionImpl('addSiteContent', function ($id, $content) use ($site) {
            if (($c = $site->getContent($id)) == null) {
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_CANT_EDIT_PAGE);
            }
            $response = new JSONResponseImpl();
            $response->setPayload($c->addContent($content));
            return $response;
        }, array('page_id', 'id', 'content')));


        $jsonServer->registerJSONFunction(new JSONFunctionImpl('listSiteContentRevisions', function($id, $from, $to, $includeContent) use ($site){
            $content = $site->getContent($id);
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

        $jsonServer->registerJSONFunction(new JSONFunctionImpl('siteContentAtTime', function($id, $time) use ($site){
            $content = $site->getContent($id);
            $response = new JSONResponseImpl();
            $response->setPayload($content->getContentAt($time));
            return $response;
        }, array('page_id', 'id', 'time')));
        return $jsonServer->evaluatePostInput()->getAsJSONString();
    }
}