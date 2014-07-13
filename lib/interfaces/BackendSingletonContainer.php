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
    public function getUpdater();

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
     * Will create and reuse instance of log.
     * @return LogFile|Null
     */
    public function getLogInstance();


    /**
     * Will Create and reuse instance of MailDomainLibrary.
     * @return MailDomainLibrary
     */
    public function getMailDomainLibraryInstance();

}
