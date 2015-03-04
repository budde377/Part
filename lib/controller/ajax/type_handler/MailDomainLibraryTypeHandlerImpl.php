<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:10 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\mail\DomainLibrary;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class MailDomainLibraryTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    use TypeHandlerTrait;

    function __construct(BackendSingletonContainer $container, DomainLibrary $library)
    {
        parent::__construct($library);
        $this->addAlias('MailDomainLibrary', ['ChristianBudde\Part\model\mail\DomainLibrary']);
        $this->whitelistType('MailDomainLibrary');
        $this->addGetInstanceFunction('MailDomainLibrary');
        $this->addFunctionAuthFunction('MailDomainLibrary', 'deleteDomain', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction('MailDomainLibrary', 'createDomain', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addTypeAuthFunction('MailDomainLibrary', $this->currentUserLoggedInAuthFunction($container));
    }


}