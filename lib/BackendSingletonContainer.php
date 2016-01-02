<?php
namespace ChristianBudde\Part;

use ChristianBudde\Part\controller\ajax\Server;
use ChristianBudde\Part\controller\ajax\TypeHandlerLibrary;
use ChristianBudde\Part\log\Logger;
use ChristianBudde\Part\model\mail\DomainLibrary;
use ChristianBudde\Part\model\page\CurrentPageStrategy;
use ChristianBudde\Part\model\page\DefaultPageLibrary;
use ChristianBudde\Part\model\page\PageOrder;
use ChristianBudde\Part\model\site\Site;
use ChristianBudde\Part\model\updater\Updater;
use ChristianBudde\Part\model\user\UserLibrary;
use ChristianBudde\Part\util\CacheControl;
use ChristianBudde\Part\util\db\DB;
use ChristianBudde\Part\util\file\FileLibrary;
use ChristianBudde\Part\util\file\Folder;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:59 AM
 * To change this template use File | Settings | File Templates.
 */
interface BackendSingletonContainer
{

    /**
     * @abstract
     * This will return a DB. The same from time to time
     * @return DB
     */
    public function getDBInstance();

    /**
     * @abstract
     * This will return an ajax register, and reuse it from time to time
     * @return Server
     */
    public function getAJAXServerInstance();


    /**
     * @abstract
     * This will return an instance of PageOrder, and reuse it.
     * @return PageOrder
     */
    public function getPageOrderInstance();


    /**
     * @abstract
     * This will return an instance of CurrentPageStrategy, and reuse it.
     * @return CurrentPageStrategy
     */
    public function getCurrentPageStrategyInstance();

    /**
     * @abstract
     * Will return an instance of Config, this might be the same as provided in constructor
     * @return Config
     */
    public function getConfigInstance();


    /**
     * @abstract
     * Will create and reuse an instance of UserLibrary
     * @return UserLibrary
     */
    public function getUserLibraryInstance();


    /**
     * Will create and reuse an instance of DefaultPageLibrary
     * @return DefaultPageLibrary
     */
    public function getDefaultPageLibraryInstance();


    /**
     * Will create and reuse an instance of CacheControl
     * @return CacheControl
     */
    public function getCacheControlInstance();

    /**
     * Will create and reuse an instance of Updater
     * @return Updater
     */
    public function getUpdaterInstance();

    /**
     * Will create and reuse an instance of Variables.
     * These should reflect the site scoped variables.
     * @return Site
     */
    public function getSiteInstance();


    /**
     * Will create and reuse an instance of FileLibrary.
     * @return FileLibrary
     */
    public function getFileLibraryInstance();

    /**
     * Will create and reuse instance of logger.
     * @return Logger
     */
    public function getLoggerInstance();

    /**
     * Will create and reuse instance of TypeHandlerLibrary.
     * @return TypeHandlerLibrary
     */
    public function getTypeHandlerLibraryInstance();


    /**
     * Will Create and reuse Folder with path pointing to tmp folder path from config
     * null if the path is empty.
     * @return Folder
     */
    public function getTmpFolderInstance();



    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name);

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value);

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name);

    /**
     * @param string $name
     * @return void
     */
    public function __unset($name);

}
