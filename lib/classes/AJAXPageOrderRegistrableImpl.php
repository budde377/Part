<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/6/13
 * Time: 12:05 AM
 * To change this template use File | Settings | File Templates.
 */

class AJAXPageOrderRegistrableImpl implements Registrable{

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
        $pageTranslator = new PageJSONObjectTranslatorImpl($pageOrder);
        $currentPage = $this->container->getCurrentPageStrategyInstance()->getCurrentPage();


        $listPagesFunction = new JSONFunctionImpl('listPages',
            function () use ($pageOrder, $currentPage, $pageTranslator) {

                $payloadArray = array();
                $recursivePageOrder =
                    function (Page $page = null) use (&$recursivePageOrder, $pageTranslator, $pageOrder) {
                        $ret = array();
                        $i = 0;
                        foreach ($pageOrder->getPageOrder($page) as $p) {
                            $ret[$i]['page'] = $pageTranslator->encode($p);
                            $ret[$i]['subpages'] = $recursivePageOrder($p);
                            $i++;
                        }
                        return $ret;
                    };
                $payloadArray['page_order'] = $recursivePageOrder();
                $inactivePages = $pageOrder->listPages(PageOrder::LIST_INACTIVE);
                $payloadArray['inactive_pages'] =
                    array_map(function (Page $p) use ($pageTranslator) {
                        return $pageTranslator->encode($p);
                    }, $inactivePages);
                $payloadArray['current_page'] = $currentPage->getID();

                $response = new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_SUCCESS);
                $response->setPayload($payloadArray);
                return $response;
            });
        $jsonServer->registerJSONFunction($listPagesFunction);

        $setPageOrderFunction = new JSONFunctionImpl('setPageOrder',
            function ($parent, array $order) use ($pageOrder) {

                if (!$this->currentUser->getUserPrivileges()->hasSitePrivileges()) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }
                $orderOK = true;
                $newOrder = array();
                foreach ($order as $id) {
                    $p = null;
                    $orderOK = $orderOK && ($p = $pageOrder->getPage($id)) != null;
                    $newOrder[] = $p;
                }
                if (!$orderOK) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR,
                        JSONResponse::ERROR_CODE_PAGE_NOT_FOUND);
                }
                $par = empty($parent) ? null : $pageOrder->getPage($parent);
                $currentOrder = $pageOrder->getPageOrder($par);
                /** @var $page Page */
                $success = true;


                foreach ($newOrder as $key => $page) {
                    if (!isset($currentOrder[$key]) || $page !== $currentOrder[$key]) {
                        $success = $success && $pageOrder->setPageOrder($page, $key, $par);
                        $startArray = array_slice($currentOrder, 0, $key);
                        $startArray[] = $page;
                        $endArray = array_slice($currentOrder, $key);
                        $index = array_search($page, $endArray, true);
                        if ($index != null) {
                            unset($endArray[$index]);
                        }
                        $currentOrder = array_merge($startArray, $endArray);
                    }
                }
                if ($success) {
                    $payload['parent'] = $par == null ? "" : $par->getID();
                    $payload['order'] = array_map(function (Page $p) {
                        return $p->getID();
                    }, $currentOrder);
                    $response = new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_SUCCESS);
                    $response->setPayload($payload);
                    return $response;
                } else {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR,
                        JSONResponse::ERROR_CODE_PAGE_ORDER_PARTIAL_SET);
                }


            }
            , array('parent', 'order'));

        $jsonServer->registerJSONFunction($setPageOrderFunction);

        return $jsonServer->evaluatePostInput()->getAsJSONString();
    }
}