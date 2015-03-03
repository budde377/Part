<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:08 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\page\Page;

class PageTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;
    private $page;

    function __construct(BackendSingletonContainer $container, Page $page)
    {
        $this->container = $container;
        $this->page = $page;
    }


}