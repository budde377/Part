<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/28/15
 * Time: 8:39 AM
 */

namespace ChristianBudde\Part\controller\ajax;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\FileLibraryTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\FileTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\ImageFileTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\LoggerTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\MailAddressLibraryTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\MailAddressTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\MailboxTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\MailDomainLibraryTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\MailDomainTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\PageContentLibraryTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\PageContentTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\PageOrderTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\PageTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\SiteContentLibraryTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\SiteContentTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\SiteTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\ajax\type_handler\UpdaterTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\UserLibraryTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\UserPrivilegesTypeHandlerImpl;
use ChristianBudde\Part\controller\ajax\type_handler\UserTypeHandlerImpl;
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
use ChristianBudde\Part\model\user\UserPrivileges;
use ChristianBudde\Part\util\file\File;
use ChristianBudde\Part\util\file\FileLibrary;
use ChristianBudde\Part\util\file\ImageFile;


class TypeHandlerLibraryImpl implements TypeHandlerLibrary
{


    private $container;
    private $keyArray = [];
    private $valueArray = [];

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
    }


    /**
     * @param PageOrder $pageOrder
     * @return TypeHandler
     */
    public function getPageOrderTypeHandlerInstance(PageOrder $pageOrder)
    {
        return $this->createInstance('PageOrder', $pageOrder, function () use ($pageOrder) {
            return new PageOrderTypeHandlerImpl($this->container, $pageOrder);
        });
    }

    /**
     * @param UserLibrary $userLibrary
     * @return TypeHandler
     */
    public function getUserLibraryTypeHandlerInstance(UserLibrary $userLibrary)
    {
        return $this->createInstance('UserLibrary', $userLibrary, function () use ($userLibrary) {
            return new UserLibraryTypeHandlerImpl($this->container, $userLibrary);
        });
    }

    /**
     * @param Logger $logger
     * @return TypeHandler
     */
    public function getLoggerTypeHandlerInstance(Logger $logger)
    {
        return $this->createInstance('Logger', $logger, function () use ($logger) {
            return new LoggerTypeHandlerImpl($this->container, $logger);
        });
    }

    /**
     * @param Updater $updater
     * @return TypeHandler
     */
    public function getUpdaterTypeHandlerInstance(Updater $updater)
    {
        return $this->createInstance('Updater', $updater, function () use ($updater) {
            return new UpdaterTypeHandlerImpl($this->container, $updater);
        });
    }

    /**
     * @param Page $page
     * @return TypeHandler
     */
    public function getPageTypeHandlerInstance(Page $page)
    {
        return $this->createInstance('Page', $page, function () use ($page) {
            return new PageTypeHandlerImpl($this->container, $page);
        });
    }

    /**
     * @param User $user
     * @return TypeHandler
     */
    public function getUserTypeHandlerInstance(User $user)
    {
        return $this->createInstance('User', $user, function () use ($user) {
            return new UserTypeHandlerImpl($this->container, $user);
        });
    }

    /**
     * @param DomainLibrary $library
     * @return TypeHandler
     */
    public function getMailDomainLibraryTypeHandlerInstance(DomainLibrary $library)
    {
        return $this->createInstance('MailDomainLibrary', $library, function () use ($library) {
            return new MailDomainLibraryTypeHandlerImpl($this->container, $library);
        });
    }

    /**
     * @param Domain $domain
     * @return TypeHandler
     */
    public function getMailDomainTypeHandlerInstance(Domain $domain)
    {
        return $this->createInstance('MailDomain', $domain, function () use ($domain) {
            return new MailDomainTypeHandlerImpl($this->container, $domain);
        });
    }

    /**
     * @param AddressLibrary $address
     * @return TypeHandler
     */
    public function getMailAddressLibraryTypeHandlerInstance(AddressLibrary $address)
    {
        return $this->createInstance('MailAddressLibrary', $address, function () use ($address) {
            return new MailAddressLibraryTypeHandlerImpl($this->container, $address);
        });
    }

    /**
     * @param Address $address
     * @return TypeHandler
     */
    public function getMailAddressTypeHandlerInstance(Address $address)
    {
        return $this->createInstance('MailAddress', $address, function () use ($address) {
            return new MailAddressTypeHandlerImpl($this->container, $address);
        });
    }

    /**
     * @param Mailbox $mailbox
     * @return TypeHandler
     */
    public function getMailboxTypeHandlerInstance(Mailbox $mailbox)
    {
        return $this->createInstance('Mailbox', $mailbox, function () use ($mailbox) {
            return new MailboxTypeHandlerImpl($this->container, $mailbox);
        });
    }

    /**
     * @param Site $site
     * @return TypeHandler
     */
    public function getSiteTypeHandlerInstance(Site $site)
    {
        return $this->createInstance('Site', $site, function () use ($site) {
            return new SiteTypeHandlerImpl($this->container, $site);
        });
    }

    /**
     * @param FileLibrary $library
     * @return TypeHandler
     */
    public function getFileLibraryTypeHandlerInstance(FileLibrary $library)
    {
        return $this->createInstance('FileLibrary', $library, function () use ($library) {
            return new FileLibraryTypeHandlerImpl($this->container, $library);
        });
    }

    /**
     * @param PageContent $content
     * @return TypeHandler
     */
    public function getPageContentTypeHandlerInstance(PageContent $content)
    {
        return $this->createInstance('PageContent', $content, function () use ($content) {
            return new PageContentTypeHandlerImpl($this->container, $content);
        });
    }

    /**
     * @param SiteContent $content
     * @return TypeHandler
     */
    public function getSiteContentTypeHandlerInstance(SiteContent $content)
    {
        return $this->createInstance('SiteContent', $content, function () use ($content) {
            return new SiteContentTypeHandlerImpl($this->container, $content);
        });
    }

    /**
     * @param SiteContentLibrary $library
     * @return TypeHandler
     */
    public function getSiteContentLibraryTypeHandlerInstance(SiteContentLibrary $library)
    {
        return $this->createInstance('SiteContentLibrary', $library, function () use ($library) {
            return new SiteContentLibraryTypeHandlerImpl($this->container, $library);
        });
    }

    /**
     * @param PageContentLibrary $library
     * @return TypeHandler
     */
    public function getPageContentLibraryTypeHandlerInstance(PageContentLibrary $library)
    {
        return $this->createInstance('PageContentLibrary', $library, function () use ($library) {
            return new PageContentLibraryTypeHandlerImpl($this->container, $library);
        });
    }

    /**
     * @param File $file
     * @return TypeHandler
     */
    public function getFileTypeHandlerInstance(File $file)
    {
        return $this->createInstance('File', $file, function () use ($file) {
            return new FileTypeHandlerImpl($this->container, $file);
        });
    }

    /**
     * @param ImageFile $file
     * @return TypeHandler
     */
    public function getImageFileTypeHandlerInstance(ImageFile $file)
    {
        return $this->createInstance('ImageFile', $file, function () use ($file) {
            return new ImageFileTypeHandlerImpl($this->container, $file);
        });
    }

    /**
     * @param $string
     * @param $instance
     * @param callable $callback
     * @return TypeHandler
     */
    private function createInstance($string, $instance, callable $callback)
    {
        if (!isset($this->keyArray[$string])) {
            $this->keyArray[$string] = [];
            $this->valueArray[$string] = [];
        } else if(($k = array_search($instance, $this->keyArray, true)) !== false){
            return $this->valueArray[$k];
        }
        $this->keyArray[] = $instance;
        return $this->valueArray[] = $callback();
    }

    /**
     * @param UserPrivileges $privileges
     * @return TypeHandler
     */
    public function getUserPrivilegesTypeHandlerInstance(UserPrivileges $privileges)
    {
        return $this->createInstance('UserPrivileges', $privileges, function () use ($privileges) {
            return new UserPrivilegesTypeHandlerImpl($this->container, $privileges);
        });
    }
}