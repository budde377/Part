<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 2:13 PM
 */

interface MailAddressLibrary {

    const LIST_MODE_ALL = 1;
    const LIST_MODE_ALIAS = 2;
    const LIST_MODE_MAILBOX = 3;

    /**
     * @param int $mode Decides which will be listed (alias, mailbox or both)
     * @return array An array containing selected entries.
     */
    public function listAddresses($mode = MailAddressLibrary::LIST_MODE_ALL);

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
     * Gets a alias from the given address. Null if not found.
     * @param string $address
     * @return MailAlias
     */
    public function getAlias($address);

    /**
     * Gets a mailbox from the given address. Null if not found.
     * @param string $address
     * @return MailMailbox
     */
    public function getMailbox($address);

    /**
     * Creates an Alias.
     * @param string $address
     * @param array $targets
     * @return MailAlias
     */
    public function createAlias($address, array $targets);

    /**
     * Creates an mailbox.
     * @param string $address
     * @return MailAddress
     */
    public function createMailbox($address);

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
     * @return MailAlias
     */
    public function getCatchallAlias();

    /**
     * @param array $targets
     * @return MailAlias
     */
    public function createCatchallAlias(array $targets);

    /**
     * @return void
     */
    public function deleteCatchallAlias();

    /**
     * @return bool
     */
    public function hasCatchallAlias();
}