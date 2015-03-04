<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:19 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\page\PageContent;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class PageContentTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;

    use TypeHandlerTrait;

    function __construct(BackendSingletonContainer $container, PageContent $content)
    {
        $this->container = $container;
        parent::__construct( $content);
        $this->addFunctionAuthFunction("PageContent", "addContent", $this->wrapFunction([$this, 'userHasPagePrivilegesAuthFunction']));
        $this->addGetInstanceFunction('PageContent');
    }


    private function userHasPagePrivilegesAuthFunction($type, PageContent $instance) {
        return
            ($current = $this->container->getUserLibraryInstance()->getUserLoggedIn()) != null &&
            $current->getUserPrivileges()->hasPagePrivileges($instance->getPage());
    }

}