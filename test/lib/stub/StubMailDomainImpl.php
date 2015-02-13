<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:12 PM
 */
namespace ChristianBudde\Part\test\stub;

use ChristianBudde\Part\model\mail\Domain;
use ChristianBudde\Part\util\Observer;


class StubMailDomainImpl implements Domain
{

    public $active;
    public $domainName;
    public $description;
    public $aliasDomain;
    public $lastModified = 0;

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
        return $this->lastModified;
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
     * @return \ChristianBudde\Part\model\mail\AddressLibrary
     */
    public function getAddressLibrary()
    {

    }

    /**
     * @return bool
     */
    public function isAliasDomain()
    {
        return $this->aliasDomain != null;
    }

    /**
     * @param \ChristianBudde\Part\model\mail\Domain $domain
     * @return void
     */
    public function setAliasTarget(Domain $domain)
    {
        $this->aliasDomain = $domain;
    }

    /**
     * @return \ChristianBudde\Part\model\mail\Domain
     */
    public function getAliasTarget()
    {
        return $this->aliasDomain;
    }

    /**
     * @return void
     */
    public function clearAliasTarget()
    {
        $this->aliasDomain = null;
    }

    /**
     * @return \ChristianBudde\Part\model\mail\DomainLibrary
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

    /**
     * Serializes the object to an instance of JSONObject.
     * @return Object
     */
    public function jsonObjectSerialize()
    {
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
    }
}