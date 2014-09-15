<?php
namespace ChristianBudde\cbweb\test\stub;use ChristianBudde;
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
    public function setCSSRegisterInstance(\ChristianBudde\cbweb\util\file\CSSRegister $CSSRegisterInstance)
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
    public function setJSRegisterInstance(\ChristianBudde\cbweb\util\file\JSRegister $JSRegisterInstance)
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
    public function setCacheControlInstance(\ChristianBudde\cbweb\util\CacheControl $cacheControlInstance)
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
    public function setCurrentPageStrategyInstance(\ChristianBudde\cbweb\model\page\CurrentPageStrategy $currentPageStrategyInstance)
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
    public function setDartRegisterInstance(\ChristianBudde\cbweb\util\file\DartRegister $dartRegisterInstance)
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
    public function setDefaultPageLibraryInstance(\ChristianBudde\cbweb\model\page\DefaultPageLibrary $defaultPageLibraryInstance)
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
    public function setPageOrderInstance(\ChristianBudde\cbweb\model\page\PageOrder $pageOrderInstance)
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
    public function setUpdater(\ChristianBudde\cbweb\model\updater\Updater $updater)
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
    public function setUserLibraryInstance(\ChristianBudde\cbweb\model\user\UserLibrary $userLibraryInstance)
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
     * @param \ChristianBudde\cbweb\controller\ajax\AJAXServer $AJAXServerInstance
     */
    public function setJAXServerInstance(\ChristianBudde\cbweb\controller\ajax\AJAXServer $AJAXServerInstance)
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
     * @return \ChristianBudde\cbweb\util\db\DB
     */
    public function getDBInstance()
    {
        return $this->DBInstance;
    }




    /**
     * @param mixed $DBInstance
     */
    public function setDBInstance(\ChristianBudde\cbweb\util\db\DB $DBInstance)
    {
        $this->DBInstance = $DBInstance;
    }

    /**
     * Will create and reuse an instance of Variables.
     * These should reflect the site scoped variables.
     * @return \ChristianBudde\cbweb\model\site\Site
     */
    public function getSiteInstance()
    {
        return $this->siteInstance;
    }

    /**
     * @param mixed $siteInstance
     */
    public function setSiteInstance(\ChristianBudde\cbweb\model\site\Site $siteInstance)
    {
        $this->siteInstance = $siteInstance;
    }


    /**
     * Will create and reuse an instance of FileLibrary.
     * @return \ChristianBudde\cbweb\util\file\FileLibrary
     */
    public function getFileLibraryInstance()
    {
        return $this->fileLibraryInstance;
    }

    /**
     * @param mixed $fileLibraryInstance
     */
    public function setFileLibraryInstance(\ChristianBudde\cbweb\util\file\FileLibrary $fileLibraryInstance)
    {
        $this->fileLibraryInstance = $fileLibraryInstance;
    }


    /**
     * Will create and reuse instance of log.
     * @return \ChristianBudde\cbweb\log\Logger
     */
    public function getLoggerInstance()
    {
        return $this->logInstance;

    }

    /**
     * @param mixed $logInstance
     */
    public function setLogInstance(\ChristianBudde\cbweb\log\Logger $logInstance)
    {
        $this->logInstance = $logInstance;
    }

}class StubBackendSingletonContainerImpl implements ChristianBudde\cbweb\BackendSingletonContainer{

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
    public function setCSSRegisterInstance(\ChristianBudde\cbweb\file\CSSRegister $CSSRegisterInstance)
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
    public function setCacheControlInstance(ChristianBudde\c\ChristianBudde\cbweb\util\cheControlInstance)
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
    public function setDartRegisterInstance(\ChristianBudde\cbweb\file\DartRegister $dartRegisterInstance)
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
    public function setPageOrderInstance(ChristianBud\ChristianBudde\cbweb\model\page\ageOrderInstance)
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
    public function setUpdater(\ChristianBudde\cbweb\Updater \ChristianBudde\cbweb\model\updater\  $this->updater = $updater;
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
     * @param \ChristianBudde\cbweb\controller\ajax\AJAXServer $AJAXServerInstance
     */
    public function setJAXServerInstance(\ChristianBudde\cbweb\controller\ajax\AJAXServer $AJAXServerInstance)
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
    publ\ChristianBudde\cbweb\util\db\nce()
    {
        return $this->DBInstance;
    }




    /**
     * @param mixed $DBInstance
     */
    public function setDBInstance(\ChristianBudde\cbweb\DB $DBInstance)
   \ChristianBudde\cbweb\util\db\stance = $DBInstance;
    }

    /**
     * Will create and reuse an instance of Variables.
     * These should reflect the site scoped variables.
     * @return \ChristianBudde\cbweb\model\site\Site
     */
    public function getSiteInstance()
    {
        return $this->siteInstance;
    }

    /**
     * @param mixed $siteInstance
     */
    public function setSiteInstance(\ChristianBudde\cbweb\model\site\Site $siteInstance)
    {
        $this->siteInstance = $siteInstance;
    }


    /**
     * Will create and reuse an instance of FileLibrary.
     * @return \ChristianBudde\cbweb\file\FileLibrary
     */
    public function getFileLibraryInstance()
    {
        return $this->fileLibraryInstance;
    }

    /**
     * @param mixed $fileLibraryInstance
     */
    public function setFileLibraryInstance(\ChristianBudde\cbweb\file\FileLibrary $fileLibraryInstance)
    {
        $this->fileLibraryInstance = $fileLibraryInstance;
    }


    /**
     * Will create and reuse instance of log.
     * @return \ChristianBudde\cbweb\logger\Logger
     */
    public function getLoggerInstance()
    {
        return $this->logInstance;

    }

    /**
     * @param mixed $logInstance
     */
    public function setLogInstance(\ChristianBudde\cbweb\logger\Logger $logInstance)
    {
        $this->logInstance = $logInstance;
    }

}