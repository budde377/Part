<?php
namespace ChristianBudde\Part;

use ChristianBudde\Part\controller\ajax\Server;
use ChristianBudde\Part\controller\ajax\ServerImpl;
use ChristianBudde\Part\controller\ajax\TypeHandlerLibrary;
use ChristianBudde\Part\controller\ajax\TypeHandlerLibraryImpl;
use ChristianBudde\Part\log\Logger;
use ChristianBudde\Part\log\LoggerImpl;
use ChristianBudde\Part\model\mail\DomainLibrary;
use ChristianBudde\Part\model\mail\DomainLibraryImpl;
use ChristianBudde\Part\model\page\CurrentPageStrategy;
use ChristianBudde\Part\model\page\CurrentPageStrategyImpl;
use ChristianBudde\Part\model\page\DefaultPageLibrary;
use ChristianBudde\Part\model\page\DefaultPageLibraryImpl;
use ChristianBudde\Part\model\page\PageOrder;
use ChristianBudde\Part\model\page\PageOrderImpl;
use ChristianBudde\Part\model\site\Site;
use ChristianBudde\Part\model\site\SiteImpl;
use ChristianBudde\Part\model\updater\GitUpdaterImpl;
use ChristianBudde\Part\model\updater\Updater;
use ChristianBudde\Part\model\user\UserLibrary;
use ChristianBudde\Part\model\user\UserLibraryImpl;
use ChristianBudde\Part\model\Variables;
use ChristianBudde\Part\util\CacheControl;
use ChristianBudde\Part\util\CacheControlImpl;
use ChristianBudde\Part\util\db\DB;
use ChristianBudde\Part\util\db\MySQLDBImpl;
use ChristianBudde\Part\util\file\CSSRegister;
use ChristianBudde\Part\util\file\CSSRegisterImpl;
use ChristianBudde\Part\util\file\DartRegister;
use ChristianBudde\Part\util\file\DartRegisterImpl;
use ChristianBudde\Part\util\file\FileLibrary;
use ChristianBudde\Part\util\file\FileLibraryImpl;
use ChristianBudde\Part\util\file\Folder;
use ChristianBudde\Part\util\file\FolderImpl;
use ChristianBudde\Part\util\file\JSRegister;
use ChristianBudde\Part\util\file\JSRegisterImpl;
use ChristianBudde\Part\util\file\LogFile;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 4:54 PM
 * To change this template use File | Settings | File Templates.
 */
class BackendSingletonContainerImpl implements BackendSingletonContainer
{

    private $config;
    /** @var $database null | DB */
    private $database = null;
    /** @var $cssRegister null | CSSRegister */
    private $cssRegister = null;
    /** @var $jsRegister null | JSRegister */
    private $jsRegister;
    /** @var null | Server */
    private $ajaxServer;
    /** @var $pageOrder null | PageOrder */
    private $pageOrder;
    /** @var $pageOrder null | CurrentPageStrategy */
    private $currentPageStrategy;
    /** @var $userLibrary null | UserLibrary */
    private $userLibrary;
    /** @var DefaultPageLibrary */
    private $defaultPageLibrary;
    /** @var DartRegister */
    private $dartRegister;
    /** @var  CacheControl */
    private $cacheControl;
    /** @var  Updater */
    private $updater;
    /** @var  Variables */
    private $site;
    /** @var  FileLibrary */
    private $fileLibrary;
    /** @var  LogFile */
    private $log;
    /** @var  DomainLibrary */
    private $mailDomainLibrary;

    private $dynamicInstances = [];
    private $typeHandlerLib;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * This will return a DB. The same from time to time
     * @return DB
     */
    public function getDBInstance()
    {
        if ($this->database === null) {
            $this->database = new MySQLDBImpl($this->config);
        }

        return $this->database;
    }

    /**
     * This will return an css register, and reuse it from time to time
     * @return CSSRegister
     */
    public function getCSSRegisterInstance()
    {
        if ($this->cssRegister === null) {
            $this->cssRegister = new CSSRegisterImpl();
        }
        return $this->cssRegister;
    }

    /**
     * This will return an js register, and reuse it from time to time
     * @return JSRegister
     */
    public function getJSRegisterInstance()
    {
        if ($this->jsRegister === null) {
            $this->jsRegister = new JSRegisterImpl();
        }
        return $this->jsRegister;
    }

    /**
     * This will return an ajax register, and reuse it from time to time
     * @return ServerImpl
     */
    public function getAJAXServerInstance()
    {
        if ($this->ajaxServer === null) {
            $this->ajaxServer = new ServerImpl($this);
        }
        return $this->ajaxServer;
    }

    /**
     * This will return an instance of PageOrder, and reuse it.
     * @return PageOrder
     */
    public function getPageOrderInstance()
    {
        if ($this->pageOrder === null) {
            $this->pageOrder = new PageOrderImpl($this);
        }
        return $this->pageOrder;
    }

    /**
     * This will return an instance of CurrentPageStrategy, and reuse it.
     * @return CurrentPageStrategy
     */
    public function getCurrentPageStrategyInstance()
    {
        if ($this->currentPageStrategy === null) {
            $this->currentPageStrategy = new CurrentPageStrategyImpl($this->getPageOrderInstance(), $this->getDefaultPageLibraryInstance());
        }
        return $this->currentPageStrategy;
    }

    /**
     * Will return an instance of Config, this might be the same as provided in constructor
     * @return Config
     */
    public function getConfigInstance()
    {
        return $this->config;
    }


    /**
     * Will create and reuse an instance of UserLibrary
     * @return UserLibrary
     */
    public function getUserLibraryInstance()
    {
        if ($this->userLibrary === null) {
            $this->userLibrary = new UserLibraryImpl($this);
        }
        return $this->userLibrary;
    }


    /**
     * Will create and reuse an instance of DefaultPageLibrary
     * @return DefaultPageLibrary
     */
    public function getDefaultPageLibraryInstance()
    {
        if ($this->defaultPageLibrary === null) {
            $this->defaultPageLibrary = new DefaultPageLibraryImpl($this->getConfigInstance());
        }

        return $this->defaultPageLibrary;
    }

    /**
     * This will return an dart register, and reuse it from time to time
     * @return DartRegister
     */
    public function getDartRegisterInstance()
    {
        if ($this->dartRegister === null) {
            $this->dartRegister = new DartRegisterImpl();
        }
        return $this->dartRegister;
    }

    /**
     * Will create and reuse an instance of CacheControl
     * @return CacheControl
     */
    public function getCacheControlInstance()
    {
        if ($this->cacheControl == null) {
            $this->cacheControl = new CacheControlImpl($this->getSiteInstance(), $this->getCurrentPageStrategyInstance());
        }
        return $this->cacheControl;
    }

    /**
     * Will create and reuse an instance of Updater
     * @return mixed
     */
    public function getUpdaterInstance()
    {
        if ($this->updater == null) {
            $this->updater = new GitUpdaterImpl($this, $this->getConfigInstance()->getRootPath());
        }
        return $this->updater;
    }

    /**
     * Will create and reuse an instance of Variables.
     * These should reflect the site scoped variables.
     * @return Site
     */
    public function getSiteInstance()
    {
        return $this->site == null ? $this->site = new SiteImpl($this->getDBInstance()) : $this->site;
    }

    /**
     * Will create and reuse an instance of FileLibrary.
     * @return FileLibrary
     */
    public function getFileLibraryInstance()
    {
        return $this->fileLibrary == null ? $this->fileLibrary = new FileLibraryImpl(new FolderImpl($this->getConfigInstance()->getRootPath() . "/files/")) : $this->fileLibrary;
    }

    /**
     * @return Logger
     */
    public function getLoggerInstance()
    {
        if ($this->log != null) {
            return $this->log;
        }


        $this->log = new LoggerImpl($this, $this->getConfigInstance()->getLogPath());

        return $this->log;
    }

    /**
     * Will Create and reuse instance of MailDomainLibrary.
     * @return DomainLibrary
     */
    public function getMailDomainLibraryInstance()
    {
        if ($this->mailDomainLibrary == null) {
            $this->mailDomainLibrary = new DomainLibraryImpl($this, $this->getConfigInstance(), $this->getDBInstance(), $this->getUserLibraryInstance());
        }

        return $this->mailDomainLibrary;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $name = strtolower($name);
        switch ($name) {
            case 'site':
                return $this->getSiteInstance();
                break;
            case 'maildomainlibrary':
                return $this->getMailDomainLibraryInstance();
                break;
            case 'ajaxserver':
                return $this->getAJAXServerInstance();
                break;
            case 'cachecontrol':
                return $this->getCacheControlInstance();
                break;
            case 'config':
                return $this->getConfigInstance();
                break;
            case 'cssregister':
                return $this->getCSSRegisterInstance();
                break;
            case 'currentpagestrategy':
                return $this->getCurrentPageStrategyInstance();
                break;
            case 'dartregister':
                return $this->getDartRegisterInstance();
                break;
            case 'db':
                return $this->getDBInstance();
                break;
            case 'defaultpagelibrary':
                return $this->getDefaultPageLibraryInstance();
                break;
            case 'jsregister':
                return $this->getJSRegisterInstance();
                break;
            case 'filelibrary':
                return $this->getFileLibraryInstance();
                break;
            case 'logger':
                return $this->getLoggerInstance();
                break;
            case 'pageorder':
                return $this->getPageOrderInstance();
                break;
            case 'updater':
                return $this->getUpdaterInstance();
                break;
            case 'userlibrary':
                return $this->getUserLibraryInstance();
                break;
            case 'tmpfolder':
                return $this->getTmpFolderInstance();
                break;

        }
        return $this->dynamicInstances[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {

        $name = strtolower($name);
        if (isset($this->dynamicInstances[$name])) {
            return;
        }
        if (is_callable($value)) {
            $value = $value($this);
        }

        $this->dynamicInstances[$name] = $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        $preDefined = [
            'site',
            'maildomainlibrary',
            'ajaxserver',
            'cachecontrol',
            'config',
            'cssregister',
            'currentpagestrategy',
            'dartregister',
            'db',
            'defaultpagelibrary',
            'jsregister',
            'filelibrary',
            'logger',
            'pageorder',
            'updater',
            'userlibrary',
            'tmpfolder'];

        $name = strtolower($name);


        return in_array($name, $preDefined) || isset($this->dynamicInstances[$name]);
    }

    /**
     * @param string $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->dynamicInstances[$name]);
    }

    /**
     * Will Create and reuse Folder with path pointing to tmp folder path from config
     * null if the path is empty.
     * @return Folder
     */
    public function getTmpFolderInstance()
    {
        return ($p = $this->getConfigInstance()->getTmpFolderPath()) != "" ? new FolderImpl($p) : null;
    }

    /**
     * Will create and reuse instance of TypeHandlerLibrary.
     * @return TypeHandlerLibrary
     */
    public function getTypeHandlerLibraryInstance()
    {
        return $this->typeHandlerLib == null ? $this->typeHandlerLib = new TypeHandlerLibraryImpl($this) : $this->typeHandlerLib;
    }
}
