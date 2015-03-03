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

class PageContentTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;
    private $content;

    function __construct(BackendSingletonContainer $container, PageContent $content)
    {
        $this->container = $container;
        $this->content = $content;
    }


}