<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:16 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\mail\Address;
use ChristianBudde\Part\model\mail\Mailbox;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class MailboxTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    use TypeHandlerTrait;

    private $container;

    function __construct(BackendSingletonContainer $container, Mailbox $mailbox)
    {
        parent::__construct($mailbox, 'Mailbox');

        $this->container = $container;
        $this->whitelistFunction('Mailbox',
            'setName',
            'getName',
            'setPassword',
            'checkPassword',
            'getAddress',
            'getAddressLibrary',
            'getDomain',
            'getInstance',
            'getDomainLibrary',
            'lastModified'
        );
        $this->addGetInstanceFunction('Mailbox');
        $this->addTypeAuthFunction('Mailbox', $this->currentUserLoggedInAuthFunction($this->container));
        $this->addFunctionAuthFunction('Mailbox', 'setName', $this->wrapFunction([$this, "isOwnerAuthFunction"]));
        $this->addFunctionAuthFunction('Mailbox', 'setPassword', $this->wrapFunction([$this, "isOwnerAuthFunction"]));
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


}