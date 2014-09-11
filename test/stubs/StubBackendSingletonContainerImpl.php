<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/15/13
 * Time: 4:57 PM
 * To change this template use File | Settings | File Templates.
 */

class StubBackendSingletonContainerImpl implements ChristianBudde\cbweb\BackendSingletonContainer{

    private $DBInstance;
    private $CSSRegisterInstance;
    private $JSRegisterInstance;
    private $AJAXServerInstance;
    private $dartRegisterInstance;
    private $pageOrderInstance;
    private $currentPageStrategyInstance;
    private $configInstance;
    private $userLibraryInstance;
    private $defaultPageLibraryInstance;
    private $cacheControlInstance;
    private $updater;
    private $siteInstance;
    private $fileLibraryInstance;
    private $logInstance;
    private $mailDomainLibraryInstance;

    /**
     * @param mixed $mailDomainLibraryInstance
     */
    public function setMailDomainLibraryInstance($mailDomainLibraryInstance)
    {
        $this->mailDomainLibraryInstance = $mailDomainLibraryInstance;
    }

    /**
     * @return mixed
     */
    public function getMailDomainLibraryInstance()
    {
        return $this->mailDomainLibraryInstance;
    }

    /**
     * @param mixed $CSSRegisterInstance
     */
    public function setCSSRegisterInstance(ChristianBudde\cbweb\CSSRegister $CSSRegisterInstance)
    {
        $this->CSSRegisterInstance = $CSSRegisterInstance;
    }

    /**
     * @return mixed
     */
    public function getCSSRegisterInstance()
    {
        return $this->CSSRegisterInstance;
    }

    /**
     * @param mixed $JSRegisterInstance
     */
    public function setJSRegisterInstance(ChristianBudde\cbweb\JSRegister $JSRegisterInstance)
    {
        $this->JSRegisterInstance = $JSRegisterInstance;
    }

    /**
     * @return mixed
     */
    public function getJSRegisterInstance()
    {
        return $this->JSRegisterInstance;
    }

    /**
     * @param mixed $cacheControlInstance
     */
    public function setCacheControlInstance(ChristianBudde\cbweb\CacheControl $cacheControlInstance)
    {
        $this->cacheControlInstance = $cacheControlInstance;
    }

    /**
     * @return mixed
     */
    public function getCacheControlInstance()
    {
        return $this->cacheControlInstance;
    }

    /**
     * @param mixed $configInstance
     */
    public function setConfigInstance(ChristianBudde\cbweb\Config $configInstance)
    {
        $this->configInstance = $configInstance;
    }

    /**
     * @return mixed
     */
    public function getConfigInstance()
    {
        return $this->configInstance;
    }

    /**
     * @param mixed $currentPageStrategyInstance
     */
    public function setCurrentPageStrategyInstance(ChristianBudde\cbweb\CurrentPageStrategy $currentPageStrategyInstance)
    {
        $this->currentPageStrategyInstance = $currentPageStrategyInstance;
    }

    /**
     * @return mixed
     */
    public function getCurrentPageStrategyInstance()
    {
        return $this->currentPageStrategyInstance;
    }

    /**
     * @param mixed $dartRegisterInstance
     */
    public function setDartRegisterInstance(ChristianBudde\cbweb\DartRegister $dartRegisterInstance)
    {
        $this->dartRegisterInstance = $dartRegisterInstance;
    }

    /**
     * @return mixed
     */
    public function getDartRegisterInstance()
    {
        return $this->dartRegisterInstance;
    }

    /**
     * @param mixed $defaultPageLibraryInstance
     */
    public function setDefaultPageLibraryInstance(ChristianBudde\cbweb\DefaultPageLibrary $defaultPageLibraryInstance)
    {
        $this->defaultPageLibraryInstance = $defaultPageLibraryInstance;
    }

    /**
     * @return mixed
     */
    public function getDefaultPageLibraryInstance()
    {
        return $this->defaultPageLibraryInstance;
    }


    /**
     * @param mixed $pageOrderInstance
     */
    public function setPageOrderInstance(ChristianBudde\cbweb\PageOrder $pageOrderInstance)
    {
        $this->pageOrderInstance = $pageOrderInstance;
    }

    /**
     * @return mixed
     */
    public function getPageOrderInstance()
    {
        return $this->pageOrderInstance;
    }

    /**
     * @param mixed $updater
     */
    public function setUpdater(\ChristianBudde\cbweb\Updater $updater)
    {
        $this->updater = $updater;
    }

    /**
     * @return mixed
     */
    public function getUpdater()
    {
        return $this->updater;
    }

    /**
     * @param mixed $userLibraryInstance
     */
    public function setUserLibraryInstance(\ChristianBudde\cbweb\UserLibrary $userLibraryInstance)
    {
        $this->userLibraryInstance = $userLibraryInstance;
    }

    /**
     * @return mixed
     */
    public function getUserLibraryInstance()
    {
        return $this->userLibraryInstance;
    }

    /**
     * @param \ChristianBudde\cbweb\AJAXServer $AJAXServerInstance
     */
    public function setJAXServerInstance(\ChristianBudde\cbweb\AJAXServer $AJAXServerInstance)
    {
        $this->AJAXServerInstance = $AJAXServerInstance;
    }

    /**
     * @return mixed
     */
    public function getAJAXServerInstance()
    {
        return $this->AJAXServerInstance;
    }


    /**
     * This will return a DB. The same from time to time
     * @return \ChristianBudde\cbweb\DB
     */
    public function getDBInstance()
    {
        return $this->DBInstance;
    }




    /**
     * @param mixed $DBInstance
     */
    public function setDBInstance(\ChristianBudde\cbweb\DB $DBInstance)
    {
        $this->DBInstance = $DBInstance;
    }

    /**
     * Will create and reuse an instance of Variables.
     * These should reflect the site scoped variables.
     * @return \ChristianBudde\cbweb\Site
     */
    public function getSiteInstance()
    {
        return $this->siteInstance;
    }

    /**
     * @param mixed $siteInstance
     */
    public function setSiteInstance(\ChristianBudde\cbweb\Site $siteInstance)
    {
        $this->siteInstance = $siteInstance;
    }


    /**
     * Will create and reuse an instance of FileLibrary.
     * @return \ChristianBudde\cbweb\FileLibrary
     */
    public function getFileLibraryInstance()
    {
        return $this->fileLibraryInstance;
    }

    /**
     * @param mixed $fileLibraryInstance
     */
    public function setFileLibraryInstance(\ChristianBudde\cbweb\FileLibrary $fileLibraryInstance)
    {
        $this->fileLibraryInstance = $fileLibraryInstance;
    }


    /**
     * Will create and reuse instance of log.
     * @return \ChristianBudde\cbweb\Logger
     */
    public function getLoggerInstance()
    {
        return $this->logInstance;

    }

    /**
     * @param mixed $logInstance
     */
    public function setLogInstance(\ChristianBudde\cbweb\Logger $logInstance)
    {
        $this->logInstance = $logInstance;
    }

}