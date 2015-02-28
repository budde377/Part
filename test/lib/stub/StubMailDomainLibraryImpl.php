<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/7/14
 * Time: 2:14 PM
 */
namespace ChristianBudde\Part\test\stub;

use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\model\mail\Domain;
use ChristianBudde\Part\model\mail\DomainLibrary;


class StubMailDomainLibraryImpl implements DomainLibrary
{

    private $listDomains;

    /**
     * @param mixed $listDomains
     */
    public function setDomainList($listDomains)
    {
        $this->listDomains = $listDomains;
    }

    /**
     * List the domains in the library as an assoc array
     * @return array An array of PostfixDomain s
     */
    public function listDomains()
    {
        return $this->listDomains;
    }

    /**
     * Will get and reuse an instance of the domain.
     * @param string $domain The domain name as a string
     * @return \ChristianBudde\Part\model\mail\Domain
     */
    public function getDomain($domain)
    {
        return isset($this->listDomains[$domain]) ? $this->listDomains[$domain] : null;
    }

    /**
     * @param string $domain
     * @param string $password
     * @return \ChristianBudde\Part\model\mail\Domain
     */
    public function createDomain($domain, $password)
    {
    }

    /**
     * Will delete the domain, if it domain is an instance in the library.
     * @param \ChristianBudde\Part\model\mail\Domain $domain
     * @param string $password
     * @return void
     */
    public function deleteDomain(Domain $domain, $password)
    {
    }

    /**
     * Check if the instance is in the library.
     * @param \ChristianBudde\Part\model\mail\Domain $domain
     * @return bool
     */
    public function containsDomain(Domain $domain)
    {
        return isset($this->listDomains[$d = $domain->getDomainName()]) && $this->listDomains[$d] === $domain;
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

    /**
     * @return TypeHandler
     */
    public function generateTypeHandler()
    {
    }
}