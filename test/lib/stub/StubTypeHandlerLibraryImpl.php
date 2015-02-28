<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/28/15
 * Time: 8:43 AM
 */

namespace ChristianBudde\Part\test\stub;


use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\ajax\TypeHandlerLibrary;
use ChristianBudde\Part\log\Logger;
use ChristianBudde\Part\model\mail\Address;
use ChristianBudde\Part\model\mail\AddressLibrary;
use ChristianBudde\Part\model\mail\Domain;
use ChristianBudde\Part\model\mail\DomainLibrary;
use ChristianBudde\Part\model\mail\Mailbox;
use ChristianBudde\Part\model\page\Page;
use ChristianBudde\Part\model\page\PageOrder;
use ChristianBudde\Part\model\updater\Updater;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\model\user\UserLibrary;

class StubTypeHandlerLibraryImpl implements TypeHandlerLibrary{

    public $typeHandlers = [];

    /**
     * @param PageOrder $pageOrder
     * @return TypeHandler
     */
    public function getPageOrderTypeHandlerInstance(PageOrder $pageOrder)
    {
        return $this->typeHandlers['PageOrder'];
    }

    /**
     * @param UserLibrary $userLibrary
     * @return TypeHandler
     */
    public function getUserLibraryTypeHandlerInstance(UserLibrary $userLibrary)
    {
        return $this->typeHandlers['UserLibrary'];
    }

    /**
     * @param Logger $logger
     * @return TypeHandler
     */
    public function getLoggerTypeHandlerInstance(Logger $logger)
    {
        return $this->typeHandlers['Logger'];
    }

    /**
     * @param Updater $updater
     * @return TypeHandler
     */
    public function getUpdaterTypeHandlerInstance(Updater $updater)
    {
        return $this->typeHandlers['Updater'];
    }

    /**
     * @param Page $page
     * @return TypeHandler
     */
    public function getPageTypeHandlerInstance(Page $page)
    {
        return $this->typeHandlers['Page'];
    }

    /**
     * @param User $user
     * @return TypeHandler
     */
    public function getUserTypeHandlerInstance(User $user)
    {
        return $this->typeHandlers['User'];
    }

    /**
     * @param DomainLibrary $library
     * @return TypeHandler
     */
    public function getMailDomainLibraryTypeHandlerInstance(DomainLibrary $library)
    {
        return $this->typeHandlers['MailDomainLibrary'];
    }

    /**
     * @param Domain $domain
     * @return TypeHandler
     */
    public function getMailDomainTypeHandlerInstance(Domain $domain)
    {
        return $this->typeHandlers['MailDomain'];
    }

    /**
     * @param AddressLibrary $address
     * @return TypeHandler
     */
    public function getMailAddressLibraryTypeHandlerInstance(AddressLibrary $address)
    {
        return $this->typeHandlers['MailAddressLibrary'];
    }

    /**
     * @param Address $address
     * @return TypeHandler
     */
    public function getMailAddressTypeHandlerInstance(Address $address)
    {
        return $this->typeHandlers['MailAddress'];
    }

    /**
     * @param Mailbox $mailbox
     * @return TypeHandler
     */
    public function getMailboxTypeHandlerInstance(Mailbox $mailbox)
    {
        return $this->typeHandlers['Mailbox'];
    }
}