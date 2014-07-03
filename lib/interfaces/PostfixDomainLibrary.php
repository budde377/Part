<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 9:27 AM
 */

interface PostfixDomainLibrary {

    /**
     * List the domains in the library as an numeric array
     * @return array An array of PostfixDomain s
     */
    public function listDomains();

    /**
     * Lists the domain alias' in the library as an numeric array.
     * @return array of PostfixDomainAlias
     */
    public function listDomainAlias();

    /**
     * Will get and reuse an instance of the domain.
     * @param string $domain The domain name as a string
     * @return PostfixDomain
     */
    public function getDomain($domain);

    /**
     * @param string $domain
     * @return PostfixDomain
     */
    public function createDomain($domain);

    /**
     * Will delete the domain, if it domain is an instance in the library.
     * @param PostfixDomain $domain
     * @return void
     */
    public function deleteDomain(PostfixDomain $domain);


} 