<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/7/14
 * Time: 2:14 PM
 */

class StubMailDomainLibraryImpl implements MailDomainLibrary{

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
     * @return MailDomain
     */
    public function getDomain($domain)
    {
        return isset($this->listDomains[$domain])?$this->listDomains[$domain]:null;
    }

    /**
     * @param string $domain
     * @param string $password
     * @return MailDomain
     */
    public function createDomain($domain, $password)
    {
    }

    /**
     * Will delete the domain, if it domain is an instance in the library.
     * @param MailDomain $domain
     * @param string $password
     * @return void
     */
    public function deleteDomain(MailDomain $domain, $password)
    {
    }

    /**
     * Check if the instance is in the library.
     * @param MailDomain $domain
     * @return bool
     */
    public function containsDomain(MailDomain $domain)
    {
        return isset($this->listDomains[$d = $domain->getDomainName()]) && $this->listDomains[$d] === $domain;
    }
}