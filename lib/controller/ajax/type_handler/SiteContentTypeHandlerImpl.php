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
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class SiteContentTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    use TypeHandlerTrait;

    function __construct(BackendSingletonContainer $container, SiteContent $content)
    {
        parent::__construct($content);
        $this->addFunctionAuthFunction("SiteContent", "addContent",  $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addGetInstanceFunction('SiteContent');
    }


}