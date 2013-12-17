<?php
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
     * @return DB
     */
    public function getDBInstance()
    {
        return null;
    }

    /**
     * This will return an css register, and reuse it from time to time
     * @return CSSRegister
     */
    public function getCSSRegisterInstance()
    {
        return null;

    }

    /**
     * This will return an js register, and reuse it from time to time
     * @return JSRegister
     */
    public function getJSRegisterInstance()
    {
        return null;
    }

    /**
     * This will return an ajax register, and reuse it from time to time
     * @return AJAXRegister
     */
    public function getAJAXRegisterInstance()
    {
        return null;
    }

    /**
     * This will return an instance of PageOrder, and reuse it.
     * @return PageOrder
     */
    public function getPageOrderInstance()
    {

        return null;
    }

    /**
     * This will return an instance of CurrentPageStrategy, and reuse it.
     * @return CurrentPageStrategy
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
     * @return UserLibrary
     */
    public function getUserLibraryInstance()
    {
        return null;
    }



    /**
     * Will create and reuse an instance of DefaultPageLibrary
     * @return DefaultPageLibrary
     */
    public function getDefaultPageLibraryInstance()
    {
        return null;
    }

    /**
     * This will return an dart register, and reuse it from time to time
     * @return DartRegister
     */
    public function getDartRegisterInstance()
    {
        return null;
    }


    /**
     * Will create and reuse an instance of CacheControl
     * @return CacheControl
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
     * @return Variables
     */
    public function getSiteInstance()
    {
        return null;
    }
}
