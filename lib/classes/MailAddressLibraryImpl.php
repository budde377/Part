<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/7/14
 * Time: 11:17 PM
 */

class MailAddressLibraryImpl implements MailAddressLibrary{

    private $db;
    private $domain;
    private $domainLibrary;

    function __construct(MailDomain $domain, MailDomainLibrary $domainLibrary, DB $db)
    {
        $this->db = $db;
        $this->domain = $domain;
        $this->domainLibrary = $domainLibrary;
    }


    /**
     * @return array An array containing selected entries.
     */
    public function listAddresses()
    {
        // TODO: Implement listAddresses() method.
    }

    /**
     * @param string $address
     * @return bool
     */
    public function hasAddress($address)
    {
        // TODO: Implement hasAddress() method.
    }

    /**
     * Gets a address from the given address. Null if not found.
     * @param string $address
     * @return MailAddress
     */
    public function getAddress($address)
    {
        // TODO: Implement getAddress() method.
    }

    /**
     * Deletes an address. It must be an instance in the library.
     * @param MailAddress $address
     * @return void
     */
    public function deleteAddress(MailAddress $address)
    {
        // TODO: Implement deleteAddress() method.
    }

    /**
     * Returns the domain associated with the address.
     * @return MailDomain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return MailAddress
     */
    public function getCatchallAddress()
    {
        // TODO: Implement getCatchallAddress() method.
    }

    /**
     * @param array $targets
     * @return MailAddress
     */
    public function createCatchallAddress(array $targets)
    {
        // TODO: Implement createCatchallAddress() method.
    }

    /**
     * @return void
     */
    public function deleteCatchallAddress()
    {
        // TODO: Implement deleteCatchallAddress() method.
    }

    /**
     * @return bool
     */
    public function hasCatchallAddress()
    {
        // TODO: Implement hasCatchallAddress() method.
    }

    /**
     * @return MailDomainLibrary
     */
    public function getDomainLibrary()
    {
        return $this->domainLibrary;
    }
}