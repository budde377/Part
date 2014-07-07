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

    function __construct(MailDomain $domain, DB $db)
    {
        $this->db = $db;
        $this->domain = $domain;
    }


    /**
     * @param int $mode Decides which will be listed (alias, mailbox or both)
     * @return array An array containing selected entries.
     */
    public function listAddresses($mode = MailAddressLibrary::LIST_MODE_ALL)
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
     * Gets a alias from the given address. Null if not found.
     * @param string $address
     * @return MailAlias
     */
    public function getAlias($address)
    {
        // TODO: Implement getAlias() method.
    }

    /**
     * Gets a mailbox from the given address. Null if not found.
     * @param string $address
     * @return MailMailbox
     */
    public function getMailbox($address)
    {
        // TODO: Implement getMailbox() method.
    }

    /**
     * Creates an Alias.
     * @param string $address
     * @param array $targets
     * @return MailAlias
     */
    public function createAlias($address, array $targets)
    {
        // TODO: Implement createAlias() method.
    }

    /**
     * Creates an mailbox.
     * @param string $address
     * @return MailAddress
     */
    public function createMailbox($address)
    {
        // TODO: Implement createMailbox() method.
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
     * @return MailAlias
     */
    public function getCatchallAlias()
    {
        // TODO: Implement getCatchallAlias() method.
    }

    /**
     * @param array $targets
     * @return MailAlias
     */
    public function createCatchallAlias(array $targets)
    {
        // TODO: Implement createCatchallAlias() method.
    }

    /**
     * @return void
     */
    public function deleteCatchallAlias()
    {
        // TODO: Implement deleteCatchallAlias() method.
    }

    /**
     * @return bool
     */
    public function hasCatchallAlias()
    {
        // TODO: Implement hasCatchallAlias() method.
    }
}