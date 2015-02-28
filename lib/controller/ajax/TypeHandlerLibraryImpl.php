<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/28/15
 * Time: 8:39 AM
 */

namespace ChristianBudde\Part\controller\ajax;


use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
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

//TODO test this

class TypeHandlerLibraryImpl implements TypeHandlerLibrary{

    /**
     * @param PageOrder $pageOrder
     * @return TypeHandler
     */
    public function getPageOrderTypeHandlerInstance(PageOrder $pageOrder)
    {
        // TODO: Implement getPageOrderTypeHandlerInstance() method.
    }

    /**
     * @param UserLibrary $userLibrary
     * @return TypeHandler
     */
    public function getUserLibraryTypeHandlerInstance(UserLibrary $userLibrary)
    {
        // TODO: Implement getUserLibraryTypeHandlerInstance() method.
    }

    /**
     * @param Logger $logger
     * @return TypeHandler
     */
    public function getLoggerTypeHandlerInstance(Logger $logger)
    {
        // TODO: Implement getLoggerTypeHandlerInstance() method.
    }

    /**
     * @param Updater $updater
     * @return TypeHandler
     */
    public function getUpdaterTypeHandlerInstance(Updater $updater)
    {
        // TODO: Implement getUpdaterTypeHandlerInstance() method.
    }

    /**
     * @param Page $page
     * @return TypeHandler
     */
    public function getPageTypeHandlerInstance(Page $page)
    {
        // TODO: Implement getPageTypeHandlerInstance() method.
    }

    /**
     * @param User $user
     * @return TypeHandler
     */
    public function getUserTypeHandlerInstance(User $user)
    {
        // TODO: Implement getUserTypeHandlerInstance() method.
    }

    /**
     * @param DomainLibrary $library
     * @return TypeHandler
     */
    public function getMailDomainLibraryTypeHandlerInstance(DomainLibrary $library)
    {
        // TODO: Implement getMailDomainLibraryTypeHandlerInstance() method.
    }

    /**
     * @param Domain $domain
     * @return TypeHandler
     */
    public function getMailDomainTypeHandlerInstance(Domain $domain)
    {
        // TODO: Implement getMailDomainTypeHandlerInstance() method.
    }

    /**
     * @param AddressLibrary $address
     * @return TypeHandler
     */
    public function getMailAddressLibraryTypeHandlerInstance(AddressLibrary $address)
    {
        // TODO: Implement getMailAddressLibraryTypeHandlerInstance() method.
    }

    /**
     * @param Address $address
     * @return TypeHandler
     */
    public function getMailAddressTypeHandlerInstance(Address $address)
    {
        // TODO: Implement getMailAddressTypeHandlerInstance() method.
    }

    /**
     * @param Mailbox $mailbox
     * @return TypeHandler
     */
    public function getMailboxTypeHandlerInstance(Mailbox $mailbox)
    {
        // TODO: Implement getMailboxTypeHandlerInstance() method.
    }
}