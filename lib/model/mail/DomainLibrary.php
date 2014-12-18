<?php
namespace ChristianBudde\cbweb\model\mail;
use ChristianBudde\cbweb\controller\json\JSONObjectSerializable;
use ChristianBudde\cbweb\util\Observable;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 9:27 AM
 */

interface DomainLibrary extends JSONObjectSerializable{

    /**
     * List the domains in the library as an assoc array
     * @return Domain[] An array of PostfixDomain s
     */
    public function listDomains();


    /**
     * Will get and reuse an instance of the domain.
     * @param string $domain The domain name as a string
     * @return Domain
     */
    public function getDomain($domain);

    /**
     * @param string $domain
     * @param string $password
     * @return Domain
     */
    public function createDomain($domain, $password);

    /**
     * Will delete the domain, if it domain is an instance in the library.
     * @param Domain $domain
     * @param string $password
     * @return void
     */
    public function deleteDomain(Domain $domain, $password);

    /**
     * Check if the instance is in the library.
     * @param Domain $domain
     * @return bool
     */
    public function containsDomain(Domain $domain);

} 