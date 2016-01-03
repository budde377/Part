<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/28/15
 * Time: 8:33 AM
 */

namespace ChristianBudde\Part\controller\ajax;


use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\log\Logger;
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

interface TypeHandlerLibrary
{
    /**
     * @param PageOrder $pageOrder
     * @return TypeHandler
     */
    public function getPageOrderTypeHandlerInstance(PageOrder $pageOrder);

    /**
     * @param UserLibrary $userLibrary
     * @return TypeHandler
     */

    public function getUserLibraryTypeHandlerInstance(UserLibrary $userLibrary);

    /**
     * @param Logger $logger
     * @return TypeHandler
     */
    public function getLoggerTypeHandlerInstance(Logger $logger);

    /**
     * @param Updater $updater
     * @return TypeHandler
     */
    public function getUpdaterTypeHandlerInstance(Updater $updater);

    /**
     * @param Page $page
     * @return TypeHandler
     */
    public function getPageTypeHandlerInstance(Page $page);

    /**
     * @param User $user
     * @return TypeHandler
     */
    public function getUserTypeHandlerInstance(User $user);

    /**
     * @param UserPrivileges $privileges
     * @return TypeHandler
     */
    public function getUserPrivilegesTypeHandlerInstance(UserPrivileges $privileges);

    /**
     * @param Site $site
     * @return TypeHandler
     */
    public function getSiteTypeHandlerInstance(Site $site);


    /**
     * @param FileLibrary $library
     * @return TypeHandler
     */
    public function getFileLibraryTypeHandlerInstance(FileLibrary  $library);

    /**
     * @param PageContent $content
     * @return TypeHandler
     */
    public function getPageContentTypeHandlerInstance(PageContent $content);

    /**
     * @param SiteContent $content
     * @return TypeHandler
     */
    public function getSiteContentTypeHandlerInstance(SiteContent $content);

    /**
     * @param SiteContentLibrary $content
     * @return TypeHandler
     */
    public function getSiteContentLibraryTypeHandlerInstance(SiteContentLibrary $content);

    /**
     * @param PageContentLibrary $content
     * @return TypeHandler
     */
    public function getPageContentLibraryTypeHandlerInstance(PageContentLibrary $content);


    /**
     * @param File $file
     * @return TypeHandler
     */
    public function getFileTypeHandlerInstance(File $file);

    /**
     * @param ImageFile $file
     * @return TypeHandler
     */
    public function getImageFileTypeHandlerInstance(ImageFile $file);

}