<?php
require_once dirname(__FILE__).'/../../_interface/BackendSingletonContainer.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/15/13
 * Time: 4:57 PM
 * To change this template use File | Settings | File Templates.
 */

class StubBackendSingletonContainerImpl implements BackendSingletonContainer{

    private $DBInstance;
    private $CSSRegisterInstance;
    private $JSRegisterInstance;
    private $AJAXRegisterInstance;
    private $dartRegisterInstance;
    private $pageOrderInstance;
    private $currentPageStrategyInstance;
    private $configInstance;
    private $siteLibraryInstance;
    private $userLibraryInstance;
    private $multiSiteUserPrivilegesLibraryInstance;
    private $defaultPageLibraryInstance;
    private $cacheControlInstance;
    private $updater;

    /**
     * @param mixed $CSSRegisterInstance
     */
    public function setCSSRegisterInstance(CSSRegister $CSSRegisterInstance)
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
    public function setJSRegisterInstance(JSRegister $JSRegisterInstance)
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
    public function setCacheControlInstance(CacheControl $cacheControlInstance)
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
    public function setConfigInstance(Config $configInstance)
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
    public function setCurrentPageStrategyInstance(CurrentPageStrategy $currentPageStrategyInstance)
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
    public function setDartRegisterInstance(DartRegister $dartRegisterInstance)
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
    public function setDefaultPageLibraryInstance(DefaultPageLibrary $defaultPageLibraryInstance)
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
     * @param mixed $multiSiteUserPrivilegesLibraryInstance
     */
    public function setMultiSiteUserPrivilegesLibraryInstance(MultiSiteUserPrivilegesLibrary $multiSiteUserPrivilegesLibraryInstance)
    {
        $this->multiSiteUserPrivilegesLibraryInstance = $multiSiteUserPrivilegesLibraryInstance;
    }

    /**
     * @return mixed
     */
    public function getMultiSiteUserPrivilegesLibraryInstance()
    {
        return $this->multiSiteUserPrivilegesLibraryInstance;
    }

    /**
     * @param mixed $pageOrderInstance
     */
    public function setPageOrderInstance(PageOrder $pageOrderInstance)
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
     * @param mixed $siteLibraryInstance
     */
    public function setSiteLibraryInstance(SiteLibrary $siteLibraryInstance)
    {
        $this->siteLibraryInstance = $siteLibraryInstance;
    }

    /**
     * @return mixed
     */
    public function getSiteLibraryInstance()
    {
        return $this->siteLibraryInstance;
    }

    /**
     * @param mixed $updater
     */
    public function setUpdater(Updater $updater)
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
    public function setUserLibraryInstance(UserLibrary $userLibraryInstance)
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
     * @param mixed $AJAXRegisterInstance
     */
    public function setAJAXRegisterInstance(AJAXRegister $AJAXRegisterInstance)
    {
        $this->AJAXRegisterInstance = $AJAXRegisterInstance;
    }

    /**
     * @return mixed
     */
    public function getAJAXRegisterInstance()
    {
        return $this->AJAXRegisterInstance;
    }


    /**
     * This will return a DB. The same from time to time
     * @return DB
     */
    public function getDBInstance()
    {
        return $this->DBInstance;
    }




    /**
     * @param mixed $DBInstance
     */
    public function setDBInstance(DB $DBInstance)
    {
        $this->DBInstance = $DBInstance;
    }
}