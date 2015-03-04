<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:04 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\site\Site;

class SiteTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    // TODO implement
    private $container;
    private $site;

    function __construct(BackendSingletonContainer $container, Site $site)
    {
        $this->container = $container;
        $this->site = $site;
    }


}