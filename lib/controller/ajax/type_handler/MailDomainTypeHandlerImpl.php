<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:12 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\mail\Domain;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class MailDomainTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    use TypeHandlerTrait;

    function __construct(BackendSingletonContainer $container, Domain $domain)
    {
        $this->container = $container;
        $this->domain = $domain;
        parent::__construct($domain);
        $this->addAlias('MailDomain', ['ChristianBudde\Part\model\mail\Domain']);
        $this->whitelistType('MailDomain');
        $this->whitelistFunction('MailDomain',
            'getDomainName',
            'isActive',
            'activate',
            'deactivate',
            'getDescription',
            'setDescription',
            'lastModified',
            'getAddressLibrary',
            'isAliasDomain',
            'setAliasTarget',
            'getInstance',
            'getAliasTarget',
            'clearAliasTarget',
            'getDomainLibrary');
        $this->addGetInstanceFunction('MailDomain');
        $this->addFunctionAuthFunction('MailDomain', 'clearAliasTarget', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction('MailDomain', 'setAliasTarget', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction('MailDomain', 'setDescription', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction('MailDomain', 'activate', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction('MailDomain', 'deactivate', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addTypeAuthFunction('MailDomainLibrary', $this->currentUserLoggedInAuthFunction($container));

    }


}