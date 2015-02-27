<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 10:40 PM
 */

namespace ChristianBudde\Part\controller\ajax;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\json\Response;
use ChristianBudde\Part\controller\json\ResponseImpl;
use ChristianBudde\Part\model\page\PageOrder;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class PageOrderTypeHandlerImpl extends GenericObjectTypeHandlerImpl
{

    private $container;

    use TypeHandlerTrait;

    function __construct(BackendSingletonContainer $container, PageOrder $order)
    {

        parent::__construct($container, 'PageOrder');
        $this->container = $container;

        $this->addGetInstanceFunction('PageOrder');

        $this->addFunctionAuthFunction('PageOrder', 'deletePage', $this->currentUserSitePrivilegesAuthFunction($this->container));
        $this->addFunctionAuthFunction('PageOrder', 'deactivatePage', $this->currentUserSitePrivilegesAuthFunction($this->container));
        $this->addFunctionAuthFunction('PageOrder', 'setPageOrder', $this->currentUserSitePrivilegesAuthFunction($this->container));
        $this->addFunctionAuthFunction('PageOrder', 'createPage', $this->currentUserSitePrivilegesAuthFunction($this->container));

        $this->addFunction('PageOrder', 'createPage', $this->wrapFunction([$this, 'createPage']));
    }


    private function createPage(PageOrder $pageOrder, $title)
    {
        if (strlen($title) == 0) {
            return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_PAGE_TITLE);
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

        return $p;

    }


}