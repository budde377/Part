<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:15 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\mail\Address;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class MailAddressTypeHandlerImpl extends GenericObjectTypeHandlerImpl
{

    use TypeHandlerTrait;

    private $container;

    function __construct(BackendSingletonContainer $container, Address $address)
    {
        $this->container = $container;
        parent::__construct($address);
        $this->addAlias('MailAddress', ['ChristianBudde\Part\model\mail\Address']);
        $this->whitelistType('MailAddress');
        $this->whitelistFunction('MailAddress',
            'getLocalPart',
            'setLocalPart',
            'isActive',
            'lastModified',
            'getDomain',
            'getAddressLibrary',
            'activate',
            'deactivate',
            'getTargets',
            'addTarget',
            'removeTarget',
            'hasTarget',
            'getMailbox',
            'hasMailbox',
            'createMailbox',
            'getInstance',
            'deleteMailbox',
            'getDomainLibrary',
            'getId',
            'addOwner',
            'removeOwner',
            'isOwner',
            'listOwners');
        $this->addGetInstanceFunction('MailAddress');
        $this->setUpAuth();
    }


    private function isOwnerAuthFunction($type, Address $instance)
    {
        $siteAuthFunction = $this->currentUserSitePrivilegesAuthFunction($this->container);
        if ($siteAuthFunction()) {
            return true;
        }
        $user = $this->container->getUserLibraryInstance()->getUserLoggedIn();
        if ($user == null) {
            return false;
        }
        return $instance->isOwner($user);
    }

    private function setUpAuth()
    {
        $this->addFunctionAuthFunction('MailAddress', 'setLocalPart', $this->currentUserSitePrivilegesAuthFunction($this->container));
        $this->addFunctionAuthFunction('MailAddress', 'addOwner', $this->currentUserSitePrivilegesAuthFunction($this->container));
        $this->addFunctionAuthFunction('MailAddress', 'removeOwner', $this->currentUserSitePrivilegesAuthFunction($this->container));
        $this->addFunctionAuthFunction('MailAddress', 'activate', $this->wrapFunction([$this, "isOwnerAuthFunction"]));
        $this->addFunctionAuthFunction('MailAddress', 'deactivate', $this->wrapFunction([$this, "isOwnerAuthFunction"]));
        $this->addFunctionAuthFunction('MailAddress', 'addTarget', $this->wrapFunction([$this, "isOwnerAuthFunction"]));
        $this->addFunctionAuthFunction('MailAddress', 'removeTarget', $this->wrapFunction([$this, "isOwnerAuthFunction"]));
        $this->addFunctionAuthFunction('MailAddress', 'createMailbox', $this->wrapFunction([$this, "isOwnerAuthFunction"]));
        $this->addFunctionAuthFunction('MailAddress', 'deleteMailbox', $this->wrapFunction([$this, "isOwnerAuthFunction"]));
        $this->addTypeAuthFunction('MailAddress', $this->currentUserLoggedInAuthFunction($this->container));

    }


}