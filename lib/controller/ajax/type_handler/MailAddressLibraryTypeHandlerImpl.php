<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:14 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\mail\AddressLibrary;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class MailAddressLibraryTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    use TypeHandlerTrait;

    function __construct(BackendSingletonContainer $container, AddressLibrary $library)
    {

        parent::__construct($library);
        $this->addAlias('MailAddressLibrary', ['ChristianBudde\Part\model\mail\AddressLibrary']);
        $this->whitelistType('MailAddressLibrary');
        $this->addGetInstanceFunction('MailAddressLibrary');
        $this->addFunctionAuthFunction('MailAddressLibrary', 'createAddress', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction('MailAddressLibrary', 'deleteAddress', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction('MailAddressLibrary', 'createCatchallAddress', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addFunctionAuthFunction('MailAddressLibrary', 'deleteCatchallAddress', $this->currentUserSitePrivilegesAuthFunction($container));
        $this->addTypeAuthFunction('MailAddressLibrary', $this->currentUserLoggedInAuthFunction($container));
    }


}