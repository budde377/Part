<?php
namespace ChristianBudde\cbweb\model\mail;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/7/14
 * Time: 11:17 PM
 */
use ChristianBudde\cbweb\util\db\DB;
use ChristianBudde\cbweb\util\Observable;
use ChristianBudde\cbweb\util\Observer;
use PDO;

class AddressLibraryImpl implements AddressLibrary, Observer{

    private $db;
    private $domain;
    private $addressList;
    private $domainName;

    private $setupStatement;

    function __construct(Domain $domain, DB $db)
    {
        $this->db = $db;
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
        foreach($this->addressList as $k=>$v){
            if($k == ""){
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
    public function hasAddress($localPart)
    {
        $this->setUpLibrary();
        return isset($this->addressList[trim($localPart)]);
    }

    /**
     * Gets a address from the given address. Null if not found.
     * @param string $localPart
     * @return Address
     */
    public function getAddress($localPart)
    {
        $localPart = trim($localPart);

        if($localPart == ''){
            return null;
        }

        $this->setUpLibrary();
        return isset($this->addressList[$localPart])?$this->addressList[$localPart]:null;

    }

    /**
     * Deletes an address. It must be an instance in the library.
     * @param Address $address
     * @return void
     */
    public function deleteAddress(Address $address)
    {
        if(!$this->contains($address)){
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
        if($this->hasAddress($localPart)){
            return $this->getAddress($localPart);
        }
        $a = new AddressImpl($localPart, $this->db, $this);
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
        return $this->hasCatchallAddress()?$this->addressList['']:null;
    }

    /**
     * @return Address
     */
    public function createCatchallAddress()
    {
        if($this->hasCatchallAddress()){
            return;
        }
        $address = new AddressImpl('', $this->db, $this);
        $address->create();
        $this->addInstance($address);
    }

    /**
     * @return void
     */
    public function deleteCatchallAddress()
    {
        if(!$this->hasCatchallAddress()){
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
        if($this->addressList != null && !$force){
            return;
        }

        $this->addressList = array();

        if($this->setupStatement == null){
            $this->setupStatement = $this->db->getConnection()->prepare("
            SELECT name
            FROM MailAddress
            WHERE
            (mailbox_id IS NULL OR (SELECT COUNT(id) FROM MailMailbox WHERE mailbox_id = id AND MailAddress.id = secondary_address_id) > 0 )
            AND domain = :domain ");
            $this->setupStatement->bindParam('domain', $this->domainName);
        }

        $this->setupStatement->execute();

        foreach($this->setupStatement->fetchAll(PDO::FETCH_ASSOC) as $row){
            $a = new AddressImpl($row['name'], $this->db, $this);
            $this->addInstance($a);
        }

    }

    /**
     * @param Address $address
     * @return bool
     */
    public function contains(Address $address)
    {
        return $this->hasAddress($l = $address->getLocalPart()) && $this->getAddress($l) === $address;
    }

    private function addInstance(AddressImpl $instance)
    {
        $this->addressList[$instance->getLocalPart()] = $instance;
        $instance->attachObserver($this);
    }

    public function onChange(Observable $subject, $changeType)
    {
        if(!($subject instanceof Address) || !$this->contains($subject)){
        }

        if($changeType == Address::EVENT_DELETE){
            unset($this->addressList[$subject->getLocalPart()]);
            $subject->detachObserver($this);
        }

        if($changeType == Address::EVENT_CHANGE_LOCAL_PART){
            $oldKey = array_search($subject, $this->addressList, true);
            $this->addressList[$subject->getLocalPart()] = $this->addressList[$oldKey];
            unset($this->addressList[$oldKey]);
        }
    }
}