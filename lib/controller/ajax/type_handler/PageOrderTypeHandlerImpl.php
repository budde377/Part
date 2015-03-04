<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 10:40 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\json\Response;
use ChristianBudde\Part\controller\json\ResponseImpl;
use ChristianBudde\Part\model\page\PageOrder;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

//TODO test this

class PageOrderTypeHandlerImpl extends GenericObjectTypeHandlerImpl
{

    private $container;

    use TypeHandlerTrait;

    function __construct(BackendSingletonContainer $container, PageOrder $order)
    {

        parent::__construct($order, 'PageOrder');
        $this->container = $container;

        $this->addGetInstanceFunction('PageOrder');

        $this->addFunctionAuthFunction('PageOrder', 'deletePage', $this->currentUserSitePrivilegesAuthFunction($this->container));
        $this->addFunctionAuthFunction('PageOrder', 'deactivatePage', $this->currentUserSitePrivilegesAuthFunction($this->container));
        $this->addFunctionAuthFunction('PageOrder', 'setPageOrder', $this->currentUserSitePrivilegesAuthFunction($this->container));
        $this->addFunctionAuthFunction('PageOrder', 'createPage', $this->currentUserSitePrivilegesAuthFunction($this->container));

        $this->addFunction('PageOrder', 'createPage', $this->createPage());
    }


    private function createPage()
    {
        return function (PageOrder $pageOrder, $title) {
            if (strlen($title) == 0) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_PAGE_TITLE);
            }
            $page_id = strtolower($title);
            $page_id = $baseId = str_replace(' ', '_', $page_id);
            $page_id = $baseId = preg_replace('/[^a-z0-9\-_]/', '', $page_id);
            $postfix = 2;
            while (($page = $pageOrder->createPage($page_id)) === false) {
                $page_id = $baseId . "_" . $postfix;
                $postfix++;
            }
            $page->setTitle($title);
            $page->setTemplate('_main');

            return $page;

        };

    }


}