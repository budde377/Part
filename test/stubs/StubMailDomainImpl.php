<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:12 PM
 */

class StubMailDomainImpl implements MailDomain{

    private $active;
    private $domainName;
    private $description;

    function __construct($active, $domainName)
    {
        $this->active = $active;
        $this->domainName = $domainName;
    }


    /**
     * Gets the current domain name.
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * Indicates if the domain is active
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return void
     */
    public function activate()
    {
        $this->active = true;
    }

    /**
     * @return void
     */
    public function deactivate()
    {
        $this->active = false;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * The last time it was modified
     * @return int A UNIX timestamp in seconds
     */
    public function lastModified()
    {
        return 0;
    }

    /**
     * The creation time
     * @return int A UNIX timestamp in seconds
     */
    public function createdAt()
    {

        return 0;
    }

    /**
     * @return bool TRUE if exists else FALSE
     */
    public function exists()
    {
        return false;
    }

    /**
     * Creates the domain if it does not exist.
     * @param string $password
     * @return bool
     */
    public function create($password)
    {

    }

    /**
     * Deletes the domain.
     * @param string $password
     * @return bool
     */
    public function delete($password)
    {

    }

    /**
     * @return MailAddressLibrary
     */
    public function getAddressLibrary()
    {

    }

    /**
     * @return bool
     */
    public function isAliasDomain()
    {

    }

    /**
     * @param MailDomain $domain
     * @return void
     */
    public function setAliasTarget(MailDomain $domain)
    {

    }

    /**
     * @return MailDomain
     */
    public function getAliasTarget()
    {

    }

    /**
     * @return void
     */
    public function clearAliasTarget()
    {

    }

    /**
     * @return MailDomainLibrary
     */
    public function getDomainLibrary()
    {

    }

    public function attachObserver(Observer $observer)
    {

    }

    public function detachObserver(Observer $observer)
    {

    }
}