<?php
namespace ChristianBudde\cbweb\test\stub;
use ChristianBudde\cbweb\BackendSingletonContainer;
use ChristianBudde\cbweb\Config;


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
     * @return \ChristianBudde\cbweb\util\db\DB
     */
    public function getDBInstance()
    {
        return null;
    }

    /**
     * This will return an css register, and reuse it from time to time
     * @return \ChristianBudde\cbweb\util\file\CSSRegister
     */
    public function getCSSRegisterInstance()
    {
        return null;

    }

    /**
     * This will return an js register, and reuse it from time to time
     * @return \ChristianBudde\cbweb\util\file\JSRegister
     */
    public function getJSRegisterInstance()
    {
        return null;
    }

    /**
     * This will return an ajax register, and reuse it from time to time
     * @return \ChristianBudde\cbweb\controller\ajax\Server
     */
    public function getAJAXServerInstance()
    {
        return null;
    }

    /**
     * This will return an instance of PageOrder, and reuse it.
     * @return \ChristianBudde\cbweb\model\page\PageOrder
     */
    public function getPageOrderInstance()
    {

        return null;
    }

    /**
     * This will return an instance of CurrentPageStrategy, and reuse it.
     * @return \ChristianBudde\cbweb\model\page\CurrentPageStrategy
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
     * @return \ChristianBudde\cbweb\model\user\UserLibrary
     */
    public function getUserLibraryInstance()
    {
        return null;
    }


    /**
     * Will create and reuse an instance of DefaultPageLibrary
     * @return \ChristianBudde\cbweb\model\page\DefaultPageLibrary
     */
    public function getDefaultPageLibraryInstance()
    {
        return null;
    }

    /**
     * This will return an dart register, and reuse it from time to time
     * @return \ChristianBudde\cbweb\util\file\DartRegister
     */
    public function getDartRegisterInstance()
    {
        return null;
    }


    /**
     * Will create and reuse an instance of CacheControl
     * @return \ChristianBudde\cbweb\util\CacheControl
     */
    public function getCacheControlInstance()
    {
        return null;
    }

    /**
     * Will create and reuse an instance of Updater
     * @return mixed
     */
    public function getUpdater()
    {
        return null;
    }

    /**
     * Will create and reuse an instance of Variables.
     * These should reflect the site scoped variables.
     * @return \ChristianBudde\cbweb\model\Variables
     */
    public function getSiteInstance()
    {
        return null;
    }

    /**
     * Will create and reuse an instance of FileLibrary.
     * @return \ChristianBudde\cbweb\util\file\FileLibrary
     */
    public function getFileLibraryInstance()
    {
        return null;
    }

    /**
     * Will create and reuse instance of log.
     * @return \ChristianBudde\cbweb\util\file\LogFile
     */
    public function getLoggerInstance()
    {
        return null;
    }

    /**
     * Will Create and reuse instance of MailDomainLibrary.
     * @return \ChristianBudde\cbweb\model\mail\DomainLibrary
     */
    public function getMailDomainLibraryInstance()
    {
        return null;
    }
}

