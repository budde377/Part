<?php
namespace ChristianBudde\cbweb\model\mail;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 2:13 PM
 */

interface MailAddressLibrary {

    /**
     * @return array An array containing selected entries.
     */
    public function listAddresses();

    /**
     * @param string $localPart
     * @return bool
     */
    public function hasAddress($localPart);

    /**
     * Gets a address from the given address. Null if not found.
     * @param string $localPart
     * @return MailAddress
     */
    public function getAddress($localPart);

    /**
     * @param string $localPart
     * @return MailAddress
     */
    public function createAddress($localPart);

    /**
     * @param MailAddress $address
     * @return bool
     */
    public function contains(MailAddress $address);

    /**
     * Deletes an address. It must be an instance in the library.
     * @param MailAddress $address
     * @return void
     */
    public function deleteAddress(MailAddress $address);


    /**
     * Returns the domain associated with the address.
     * @return MailDomain
     */
    public function getDomain();

    /**
     * @return MailAddress
     */
    public function getCatchallAddress();

    /**
     * @return MailAddress
     */
    public function createCatchallAddress();

    /**
     * @return void
     */
    public function deleteCatchallAddress();

    /**
     * @return bool
     */
    public function hasCatchallAddress();


    /**
     * @return MailDomainLibrary
     */
    public function getDomainLibrary();

}