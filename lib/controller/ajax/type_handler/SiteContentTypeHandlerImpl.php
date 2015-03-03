<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:20 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\site\SiteContent;

class SiteContentTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;
    private $content;

    function __construct(BackendSingletonContainer $container, SiteContent $content)
    {
        $this->container = $container;
        $this->content = $content;
    }


}