<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 9:32 AM
 */

interface PostfixDomain {
    const EVENT_DOMAIN_NAME_UPDATE = 1;
    const EVENT_DELETE = 2;



    /**
     * Gets the current domain name.
     * @return string
     */
    public function getDomainName();

    /**
     * Sets the domain name
     * @param string $name
     * @return void
     */
    public function setDomainName($name);

    /**
     * Indicates if the domain is active
     * @return bool
     */
    public function isActive();

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
     * @return mixed
     */
    public function create();

    /**
     * Deletes the domain.
     * @return void
     */
    public function delete();

    /**
     * @return PostfixAddressLibrary
     */
    public function getAddressLibrary();


    /**
     * @return bool
     */
    public function isAliasDomain();

    /**
     * @param PostfixDomain $domain
     * @return void
     */
    public function setAliasTarget(PostfixDomain $domain);

    /**
     * @return PostfixDomain
     */
    public function getAliasTarget();



}