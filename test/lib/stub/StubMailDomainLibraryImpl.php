<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/7/14
 * Time: 2:14 PM
 */
namespace ChristianBudde\cbweb\test\stub;

use ChristianBudde\cbweb\model\mail\Domain;
use ChristianBudde\cbweb\model\mail\DomainLibrary;


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
     * @return \ChristianBudde\cbweb\model\mail\Domain
     */
    public function getDomain($domain)
    {
        return isset($this->listDomains[$domain]) ? $this->listDomains[$domain] : null;
    }

    /**
     * @param string $domain
     * @param string $password
     * @return \ChristianBudde\cbweb\model\mail\Domain
     */
    public function createDomain($domain, $password)
    {
    }

    /**
     * Will delete the domain, if it domain is an instance in the library.
     * @param \ChristianBudde\cbweb\model\mail\Domain $domain
     * @param string $password
     * @return void
     */
    public function deleteDomain(Domain $domain, $password)
    {
    }

    /**
     * Check if the instance is in the library.
     * @param \ChristianBudde\cbweb\model\mail\Domain $domain
     * @return bool
     */
    public function containsDomain(Domain $domain)
    {
        return isset($this->listDomains[$d = $domain->getDomainName()]) && $this->listDomains[$d] === $domain;
    }
}