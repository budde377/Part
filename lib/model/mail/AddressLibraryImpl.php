<?php
namespace ChristianBudde\Part\model\mail;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/7/14
 * Time: 11:17 PM
 */

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\MailAddressLibraryObjectImpl;
use ChristianBudde\Part\util\Observable;
use ChristianBudde\Part\util\Observer;
use PDO;

class AddressLibraryImpl implements AddressLibrary, Observer
{

    private $domain;
    private $addressList;
    private $domainName;

    private $container;

    function __construct(BackendSingletonContainer $container, Domain $domain)
    {
        $this->container = $container;
        $this->domain = $domain;
        $this->domainName = $domain->getDomainName();

    }


    /**
     * @return Address[] An array containing selected entries.
     */
    public function listAddresses()
    {
        $this->setUpLibrary();
        $a = array();
        foreach ($this->addressList as $k => $v) {
            if ($k == "") {
                continue;
            }
            $a[$k] = $v;
        }
        return $a;
    }

    /**
     * @param string $localPart
     * @return bool
     */
    public function hasAddressWithLocalPart($localPart)
    {
        $this->setUpLibrary();
        return isset($this->addressList[trim($localPart)]);
    }

    /**
     * @param Address $address
     * @return bool
     */
    public function hasAddress(Address $address)
    {
        return array_search($address, $this->addressList, true) !== false;
    }

    /**
     * Gets a address from the given address. Null if not found.
     * @param string $localPart
     * @return Address
     */
    public function getAddress($localPart)
    {
        $localPart = trim($localPart);

        if ($localPart == '') {
            return null;
        }

        $this->setUpLibrary();
        return isset($this->addressList[$localPart]) ? $this->addressList[$localPart] : null;

    }

    /**
     * Deletes an address. It must be an instance in the library.
     * @param Address $address
     * @return void
     */
    public function deleteAddress(Address $address)
    {
        if ($address === $this->getCatchallAddress()) {
            $this->deleteCatchallAddress();
            return;
        }

        if (!$this->contains($address)) {
            return;
        }

        $address->delete();
    }

    /**
     * @param string $localPart
     * @return Address
     */
    public function createAddress($localPart)
    {
        if ($this->hasAddressWithLocalPart($localPart)) {
            return $this->getAddress($localPart);
        }
        $a = new AddressImpl($this->container, $localPart, $this);
        $a->create();
        $this->addInstance($a);
        return $a;
    }

    /**
     * Returns the domain associated with the address.
     * @return Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return Address
     */
    public function getCatchallAddress()
    {
        return $this->hasCatchallAddress() ? $this->addressList[''] : null;
    }

    /**
     * @return Address
     */
    public function createCatchallAddress()
    {
        if ($this->hasCatchallAddress()) {
            return $this->getCatchallAddress();
        }
        $address = new AddressImpl($this->container, '', $this);
        $address->create();
        $this->addInstance($address);
        return $address;
    }

    /**
     * @return void
     */
    public function deleteCatchallAddress()
    {
        if (!$this->hasCatchallAddress()) {
            return;
        }

        $this->getCatchallAddress()->delete();
    }

    /**
     * @return bool
     */
    public function hasCatchallAddress()
    {
        $this->setUpLibrary();
        return isset($this->addressList['']);
    }

    /**
     * @return DomainLibrary
     */
    public function getDomainLibrary()
    {
        return $this->domain->getDomainLibrary();
    }

    private function setUpLibrary($force = false)
    {
        if ($this->addressList != null && !$force) {
            return;
        }

        $this->addressList = array();
        $setupStatement = $this->container
            ->getDBInstance()
            ->getConnection()
            ->prepare("
            SELECT name
            FROM MailAddress
            WHERE
            (mailbox_id IS NULL OR (SELECT COUNT(id) FROM MailMailbox WHERE mailbox_id = id AND MailAddress.id = secondary_address_id) > 0 )
            AND domain = :domain ");
        $setupStatement->bindParam('domain', $this->domainName);

        $setupStatement->execute();

        foreach ($setupStatement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $a = new AddressImpl($this->container, $row['name'], $this);
            $this->addInstance($a);
        }

    }

    /**
     * @param Address $address
     * @return bool
     */
    public function contains(Address $address)
    {
        return $this->hasAddress($address);
    }

    private function addInstance(AddressImpl $instance)
    {
        $this->addressList[$instance->getLocalPart()] = $instance;
        $instance->attachObserver($this);
    }

    public function onChange(Observable $subject, $changeType)
    {
        if (!($subject instanceof Address) || !$this->contains($subject)) {
            //todo fix
            return;
        }

        if ($changeType == Address::EVENT_DELETE) {
            unset($this->addressList[$subject->getLocalPart()]);
            $subject->detachObserver($this);
        }

        if ($changeType == Address::EVENT_CHANGE_LOCAL_PART) {
            $oldKey = array_search($subject, $this->addressList, true);
            $this->addressList[$subject->getLocalPart()] = $this->addressList[$oldKey];
            unset($this->addressList[$oldKey]);
        }
    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return Object
     */
    public function jsonObjectSerialize()
    {
        return new MailAddressLibraryObjectImpl($this);
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return $this->jsonObjectSerialize()->jsonSerialize();
    }

    /**
     * @return TypeHandler
     */
    public function generateTypeHandler()
    {
        return $this->container->getTypeHandlerLibraryInstance()->getMailAddressLibraryTypeHandlerInstance($this);
    }
}