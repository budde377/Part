<?php
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
     * This will return an css register, and reuse it from time to time
     * @return CSSRegister
     */
    public function getCSSRegisterInstance();

    /**
     * @abstract
     * This will return an js register, and reuse it from time to time
     * @return JSRegister
     */
    public function getJSRegisterInstance();

    /**
     * @abstract
     * This will return an ajax register, and reuse it from time to time
     * @return AJAXRegister
     */
    public function getAJAXRegisterInstance();

    /**
     * This will return an dart register, and reuse it from time to time
     * @return DartRegister
     */
    public function getDartRegisterInstance();


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
     * Will create and reuse an instance of SiteLibrary
     * @return SiteLibrary
     */
    public function getSiteLibraryInstance();

    /**
     * @abstract
     * Will create and reuse an instance of UserLibrary
     * @return UserLibrary
     */
    public function getUserLibraryInstance();


    /**
     * @abstract
     * Will create and reuse an instance of UserPrivilegesLibrary
     * @return MultiSiteUserPrivilegesLibrary
     */
    public function getMultiSiteUserPrivilegesLibraryInstance();

    /**
     * Will create and reuse an instance of DefaultPageLibrary
     * @return DefaultPageLibrary
     */
    public function getDefaultPageLibraryInstance();



}
