<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/4/14
 * Time: 11:22 PM
 */

class PostfixDomainLibraryImpl implements PostfixDomainLibrary{

    /**
     * List the domains in the library as an numeric array
     * @return array An array of PostfixDomain s
     */
    public function listDomains()
    {
        // TODO: Implement listDomains() method.
    }

    /**
     * Lists the domain alias' in the library as an numeric array.
     * @return array of PostfixDomainAlias
     */
    public function listDomainAlias()
    {
        // TODO: Implement listDomainAlias() method.
    }

    /**
     * Will get and reuse an instance of the domain.
     * @param string $domain The domain name as a string
     * @return PostfixDomain
     */
    public function getDomain($domain)
    {
        // TODO: Implement getDomain() method.
    }

    /**
     * @param string $domain
     * @return PostfixDomain
     */
    public function createDomain($domain)
    {
        // TODO: Implement createDomain() method.
    }

    /**
     * Will delete the domain, if it domain is an instance in the library.
     * @param PostfixDomain $domain
     * @return void
     */
    public function deleteDomain(PostfixDomain $domain)
    {
        // TODO: Implement deleteDomain() method.
    }
}