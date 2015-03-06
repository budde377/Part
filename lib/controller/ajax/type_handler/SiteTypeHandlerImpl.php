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
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class SiteTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    use TypeHandlerTrait;

    private $container;

    function __construct(BackendSingletonContainer $container, Site $site)
    {
        $this->container = $container;

        parent::__construct($site, 'Site');

        $this->whitelistFunction('Site',
            'getContent',
            'lastModified',
            'modify',
            'getContentLibrary');

        $this->addFunctionAuthFunction('Site', 'modify', $this->currentUserSitePrivilegesAuthFunction($container));


    }


}