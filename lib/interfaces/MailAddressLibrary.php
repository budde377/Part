<?php
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
     * @param string $address
     * @return bool
     */
    public function hasAddress($address);

    /**
     * Gets a address from the given address. Null if not found.
     * @param string $address
     * @return MailAddress
     */
    public function getAddress($address);



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
     * @param array $targets
     * @return MailAddress
     */
    public function createCatchallAddress(array $targets);

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