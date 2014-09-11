<?php
namespace ChristianBudde\cbweb;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 9:32 AM
 */

interface MailDomain extends Observable{
    const EVENT_DELETE = 2;



    /**
     * Gets the current domain name.
     * @return string
     */
    public function getDomainName();


    /**
     * Indicates if the domain is active
     * @return bool
     */
    public function isActive();

    /**
     * @return void
     */
    public function activate();

    /**
     * @return void
     */
    public function deactivate();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description);

    /**
     * The last time it was modified
     * @return int A UNIX timestamp in seconds
     */
    public function lastModified();

    /**
     * The creation time
     * @return int A UNIX timestamp in seconds
     */
    public function createdAt();

    /**
     * @return bool TRUE if exists else FALSE
     */
    public function exists();

    /**
     * Creates the domain if it does not exist.
     * @param string $password
     * @return bool
     */
    public function create($password);

    /**
     * Deletes the domain.
     * @param string $password
     * @return bool
     */
    public function delete($password);

    /**
     * @return MailAddressLibrary
     */
    public function getAddressLibrary();


    /**
     * @return bool
     */
    public function isAliasDomain();

    /**
     * @param MailDomain $domain
     * @return void
     */
    public function setAliasTarget(MailDomain $domain);

    /**
     * @return MailDomain
     */
    public function getAliasTarget();

    /**
     * @return void
     */
    public function clearAliasTarget();


    /**
     * @return MailDomainLibrary
     */
    public function getDomainLibrary();


}