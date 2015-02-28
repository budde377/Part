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
use ChristianBudde\Part\model\page\PageContent;
use ChristianBudde\Part\model\page\PageContentLibrary;
use ChristianBudde\Part\model\page\PageOrder;
use ChristianBudde\Part\model\site\Site;
use ChristianBudde\Part\model\site\SiteContent;
use ChristianBudde\Part\model\site\SiteContentLibrary;
use ChristianBudde\Part\model\updater\Updater;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\model\user\UserLibrary;
use ChristianBudde\Part\util\file\File;
use ChristianBudde\Part\util\file\FileLibrary;
use ChristianBudde\Part\util\file\ImageFile;

class StubTypeHandlerLibraryImpl implements TypeHandlerLibrary{


    /**
     * @param PageOrder $pageOrder
     * @return TypeHandler
     */
    public function getPageOrderTypeHandlerInstance(PageOrder $pageOrder)
    {
        return $pageOrder;
    }

    /**
     * @param UserLibrary $userLibrary
     * @return TypeHandler
     */
    public function getUserLibraryTypeHandlerInstance(UserLibrary $userLibrary)
    {
        return $userLibrary;
    }

    /**
     * @param Logger $logger
     * @return TypeHandler
     */
    public function getLoggerTypeHandlerInstance(Logger $logger)
    {
        return $logger;
    }

    /**
     * @param Updater $updater
     * @return TypeHandler
     */
    public function getUpdaterTypeHandlerInstance(Updater $updater)
    {
        return $updater;
    }

    /**
     * @param Page $page
     * @return TypeHandler
     */
    public function getPageTypeHandlerInstance(Page $page)
    {
        return $page;
    }

    /**
     * @param User $user
     * @return TypeHandler
     */
    public function getUserTypeHandlerInstance(User $user)
    {
        return $user;
    }

    /**
     * @param DomainLibrary $library
     * @return TypeHandler
     */
    public function getMailDomainLibraryTypeHandlerInstance(DomainLibrary $library)
    {
        return $library;
    }

    /**
     * @param Domain $domain
     * @return TypeHandler
     */
    public function getMailDomainTypeHandlerInstance(Domain $domain)
    {
        return $domain;
    }

    /**
     * @param AddressLibrary $address
     * @return TypeHandler
     */
    public function getMailAddressLibraryTypeHandlerInstance(AddressLibrary $address)
    {
        return $address;
    }

    /**
     * @param Address $address
     * @return TypeHandler
     */
    public function getMailAddressTypeHandlerInstance(Address $address)
    {
        return $address;
    }

    /**
     * @param Mailbox $mailbox
     * @return TypeHandler
     */
    public function getMailboxTypeHandlerInstance(Mailbox $mailbox)
    {
        return $mailbox;
    }

    /**
     * @param Site $site
     * @return TypeHandler
     */
    public function getSiteTypeHandlerInstance(Site $site)
    {
        return $site;
    }

    /**
     * @param FileLibrary $library
     * @return TypeHandler
     */
    public function getFileLibraryTypeHandlerInstance(FileLibrary $library)
    {
        return $library;
    }

    /**
     * @param PageContent $content
     * @return TypeHandler
     */
    public function getPageContentTypeHandlerInstance(PageContent $content)
    {
        return $content;
    }

    /**
     * @param SiteContent $content
     * @return TypeHandler
     */
    public function getSiteContentTypeHandlerInstance(SiteContent $content)
    {
        return $content;
    }

    /**
     * @param SiteContentLibrary $content
     * @return TypeHandler
     */
    public function getSiteContentLibraryTypeHandlerInstance(SiteContentLibrary $content)
    {
        return $content;
    }

    /**
     * @param PageContentLibrary $content
     * @return TypeHandler
     */
    public function getPageContentLibraryTypeHandlerInstance(PageContentLibrary $content)
    {
        return $content;
    }

    /**
     * @param File $file
     * @return TypeHandler
     */
    public function getFileTypeHandlerInstance(File $file)
    {
        return $file;
    }

    /**
     * @param ImageFile $file
     * @return TypeHandler
     */
    public function getImageFileTypeHandlerInstance(ImageFile $file)
    {
        return $file;
    }
}