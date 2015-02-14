<?php
namespace ChristianBudde\Part\test\stub;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\Config;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 5:21 PM
 * To change this template use File | Settings | File Templates.
 */
class NullBackendSingletonContainerImpl implements BackendSingletonContainer
{

    /**
     * This will return a DB. The same from time to time
     * @return \ChristianBudde\Part\util\db\DB
     */
    public function getDBInstance()
    {
        return null;
    }

    /**
     * This will return an css register, and reuse it from time to time
     * @return \ChristianBudde\Part\util\file\CSSRegister
     */
    public function getCSSRegisterInstance()
    {
        return null;

    }

    /**
     * This will return an js register, and reuse it from time to time
     * @return \ChristianBudde\Part\util\file\JSRegister
     */
    public function getJSRegisterInstance()
    {
        return null;
    }

    /**
     * This will return an ajax register, and reuse it from time to time
     * @return \ChristianBudde\Part\controller\ajax\Server
     */
    public function getAJAXServerInstance()
    {
        return null;
    }

    /**
     * This will return an instance of PageOrder, and reuse it.
     * @return \ChristianBudde\Part\model\page\PageOrder
     */
    public function getPageOrderInstance()
    {

        return null;
    }

    /**
     * This will return an instance of CurrentPageStrategy, and reuse it.
     * @return \ChristianBudde\Part\model\page\CurrentPageStrategy
     */
    public function getCurrentPageStrategyInstance()
    {
        return null;
    }

    /**
     * Will return an instance of Config, this might be the same as provided in constructor
     * @return Config
     */
    public function getConfigInstance()
    {
        return null;
    }


    /**
     * Will create and reuse an instance of UserLibrary
     * @return \ChristianBudde\Part\model\user\UserLibrary
     */
    public function getUserLibraryInstance()
    {
        return null;
    }


    /**
     * Will create and reuse an instance of DefaultPageLibrary
     * @return \ChristianBudde\Part\model\page\DefaultPageLibrary
     */
    public function getDefaultPageLibraryInstance()
    {
        return null;
    }

    /**
     * This will return an dart register, and reuse it from time to time
     * @return \ChristianBudde\Part\util\file\DartRegister
     */
    public function getDartRegisterInstance()
    {
        return null;
    }


    /**
     * Will create and reuse an instance of CacheControl
     * @return \ChristianBudde\Part\util\CacheControl
     */
    public function getCacheControlInstance()
    {
        return null;
    }

    /**
     * Will create and reuse an instance of Updater
     * @return mixed
     */
    public function getUpdaterInstance()
    {
        return null;
    }

    /**
     * Will create and reuse an instance of Variables.
     * These should reflect the site scoped variables.
     * @return \ChristianBudde\Part\model\Variables
     */
    public function getSiteInstance()
    {
        return null;
    }

    /**
     * Will create and reuse an instance of FileLibrary.
     * @return \ChristianBudde\Part\util\file\FileLibrary
     */
    public function getFileLibraryInstance()
    {
        return null;
    }

    /**
     * Will create and reuse instance of log.
     * @return \ChristianBudde\Part\util\file\LogFile
     */
    public function getLoggerInstance()
    {
        return null;
    }

    /**
     * Will Create and reuse instance of MailDomainLibrary.
     * @return \ChristianBudde\Part\model\mail\DomainLibrary
     */
    public function getMailDomainLibraryInstance()
    {
        return null;
    }
}

