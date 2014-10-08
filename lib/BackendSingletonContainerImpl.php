<?php
namespace ChristianBudde\cbweb;
use ChristianBudde\cbweb\controller\ajax\Server;
use ChristianBudde\cbweb\controller\ajax\ServerImpl;
use ChristianBudde\cbweb\util\CacheControl;
use ChristianBudde\cbweb\util\CacheControlImpl;
use ChristianBudde\cbweb\util\file\CSSRegister;
use ChristianBudde\cbweb\util\file\CSSRegisterImpl;
use ChristianBudde\cbweb\util\file\DartRegister;
use ChristianBudde\cbweb\util\file\DartRegisterImpl;
use ChristianBudde\cbweb\util\file\FileLibrary;
use ChristianBudde\cbweb\util\file\FileLibraryImpl;
use ChristianBudde\cbweb\util\file\FolderImpl;
use ChristianBudde\cbweb\util\file\JSRegister;
use ChristianBudde\cbweb\util\file\JSRegisterImpl;
use ChristianBudde\cbweb\util\file\LogFile;
use ChristianBudde\cbweb\log\Logger;
use ChristianBudde\cbweb\log\LoggerImpl;
use ChristianBudde\cbweb\model\mail\DomainLibrary;
use ChristianBudde\cbweb\model\mail\DomainLibraryImpl;
use ChristianBudde\cbweb\model\page\CurrentPageStrategy;
use ChristianBudde\cbweb\model\page\CurrentPageStrategyImpl;
use ChristianBudde\cbweb\model\page\DefaultPageLibrary;
use ChristianBudde\cbweb\model\page\DefaultPageLibraryImpl;
use ChristianBudde\cbweb\model\page\PageOrder;
use ChristianBudde\cbweb\model\page\PageOrderImpl;
use ChristianBudde\cbweb\model\site\Site;
use ChristianBudde\cbweb\model\site\SiteImpl;
use ChristianBudde\cbweb\model\updater\GitUpdaterImpl;
use ChristianBudde\cbweb\model\updater\Updater;
use ChristianBudde\cbweb\model\user\UserLibrary;
use ChristianBudde\cbweb\model\user\UserLibraryImpl;
use ChristianBudde\cbweb\model\Variables;
use ChristianBudde\cbweb\util\db\DB;
use ChristianBudde\cbweb\util\db\MySQLDBImpl;

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
            $this->userLibrary = new UserLibraryImpl($this->getDBInstance());
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
    public function getUpdater()
    {
        if ($this->updater == null) {
            $this->updater = new GitUpdaterImpl($this->getConfigInstance()->getRootPath(), $this->getSiteInstance());
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


        $this->log = new LoggerImpl($this->getConfigInstance()->getLogPath());

        return $this->log;
    }

    /**
     * Will Create and reuse instance of MailDomainLibrary.
     * @return DomainLibrary
     */
    public function getMailDomainLibraryInstance()
    {
        if ($this->mailDomainLibrary == null) {
            $this->mailDomainLibrary = new DomainLibraryImpl($this->getConfigInstance(), $this->getDBInstance());
        }

        return $this->mailDomainLibrary;
    }
}
