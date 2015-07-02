<?php
namespace ChristianBudde\Part\model\mail;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\MailAddressObjectImpl;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\util\Observable;
use ChristianBudde\Part\util\Observer;
use ChristianBudde\Part\util\ObserverLibrary;
use ChristianBudde\Part\util\ObserverLibraryImpl;
use ChristianBudde\Part\util\traits\ValidationTrait;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:02 PM
 */
class AddressImpl implements Address, Observer, \Serializable
{

    use ValidationTrait;

    /** @var  ObserverLibrary */
    private $observerLibrary;

    private $localPart;
    private $database;
    private $addressLibrary;
    private $active = true;
    private $hasBeenSetup = false;
    private $domainName;
    /** @var  Mailbox */
    private $mailbox;
    private $aliasList;
    private $addressId;

    private $modified = 0;
    private $created = 0;
    /** @var  PDOStatement */
    private $existsStatement;
    private $deleteStatement;
    private $createStatement;
    private $saveStatement;
    private $setupAliasStatement;
    private $removeAliasStatement;
    private $updLastModStm;
    private $addTargetStatement;
    private $clearTargetsStm;
    private $setUpOwnerStatement;
    private $addOwnerStatement;
    private $removeOwnerStatement;
    private $owners = [];
    private $userLibrary;
    private $container;

    function __construct(BackendSingletonContainer $container, $localPart, AddressLibrary $addressLibrary)
    {
        $this->container = $container;
        $this->observerLibrary = new ObserverLibraryImpl($this);
        $this->addressLibrary = $addressLibrary;
        $this->database = $container->getDBInstance();
        $this->localPart = $localPart;
        $this->domainName = $addressLibrary->getDomain()->getDomainName();
        $this->userLibrary = $container->getUserLibraryInstance();
    }


    /**
     * @return string
     */
    public function getLocalPart()
    {
        return $this->localPart;
    }

    /**
     * @param $localPart
     * @return bool
     */
    public function setLocalPart($localPart)
    {
        $localPart = trim($localPart);

        if (!$this->validMail("$localPart@example.org")) {
            return false;
        }
        $this->setUp();
        $oldName = $this->localPart;
        $this->localPart = $localPart;
        if (!$this->saveAddress()) {
            $this->localPart = $oldName;
            return false;
        }
        $this->callObservers(Address::EVENT_CHANGE_LOCAL_PART);
        return true;
    }

    /**
     * Indicates if the address is active
     * @return bool
     */
    public function isActive()
    {
        $this->setUp();
        return $this->active;
    }

    /**
     * Last modified
     * @return int UNIX timestamp in seconds.
     */
    public function lastModified()
    {
        $this->setUp();
        return $this->modified;
    }

    /**
     * Creation time
     * @return int UNIX timestamp in seconds.
     */
    public function createdAt()
    {
        $this->setUp();
        return $this->created;
    }

    /**
     * Checks if the address exists
     * @return bool
     */
    public function exists()
    {
        if ($this->existsStatement == null) {
            $this->existsStatement = $this->database->getConnection()->prepare("SELECT * FROM MailAddress WHERE domain = :domain AND name = :local_part");
            $this->existsStatement->bindParam("domain", $this->domainName);
            $this->existsStatement->bindParam("local_part", $this->localPart);
        }

        $this->existsStatement->execute();

        return $this->existsStatement->rowCount() > 0;

    }

    /**
     * Deletes an address
     * @return void
     */
    public function delete()
    {

        $this->setUp();
        if ($this->addressId == null) {
            return;
        }

        if ($this->deleteStatement == null) {
            $this->deleteStatement = $this->database->getConnection()->prepare("DELETE FROM MailAddress WHERE id = :id");
            $this->deleteStatement->bindParam('id', $this->addressId);
        }
        $this->deleteStatement->execute();
        $this->callObservers(Address::EVENT_DELETE);

        $this->owners = [];
        $this->aliasList = [];
    }

    /**
     * Creates an address
     * @return void
     */
    public function create()
    {

        if ($this->exists()) {
            return;
        }

        if ($this->createStatement == null) {
            $this->createStatement = $this->database->getConnection()->prepare("
            INSERT INTO MailAddress (name, domain, id, mailbox_id, created, modified, active)
            VALUES (:name, :domain, :id, NULL, NOW(), NOW(), :active)");
        }
        $this->addressId = uniqid('address', true);
        $this->createStatement->execute(array(
            ':name' => $this->localPart,
            ':domain' => $this->domainName,
            ':id' => $this->addressId,
            ':active' => $this->active ? 1 : 0));

        $this->loadTimestamps();
    }

    /**
     * @return Domain
     */
    public function getDomain()
    {
        return $this->addressLibrary->getDomain();
    }

    /**
     * @return void
     */
    public function activate()
    {
        $this->setUp();
        $this->active = true;
        $this->saveAddress();
    }

    /**
     * @return void
     */
    public function deactivate()
    {
        $this->setUp();
        $this->active = false;
        $this->saveAddress();
    }

    /**
     * @return array An array of strings containing targets. This should be a numeric array.
     */
    public function getTargets()
    {
        $this->setUpAlias();
        return array_values($this->aliasList);
    }

    /**
     * Adds an target if it doesn't exists
     * @param string $address
     * @return void
     */
    public function addTarget($address)
    {
        $address = trim($address);
        if (!$this->validMail($address)) {
            return;
        }
        if ($this->hasTarget($address)) {
            return;
        }
        if ($this->addTargetStatement == null) {
            $this->addTargetStatement = $this->database->getConnection()->prepare("INSERT INTO MailAlias (address_id, target) VALUES (:id, :target)");

        }
        $this->addTargetStatement->execute(array('id' => $this->addressId, 'target' => $address));
        $this->aliasList[$address] = $address;
    }

    /**
     * Removes a target if exists.
     * @param string $address
     * @return void
     */
    public function removeTarget($address)
    {
        $this->setUpAlias();
        $address = trim($address);
        if (!$this->hasTarget($address)) {
            return;
        }

        if ($this->removeAliasStatement == null) {
            $this->removeAliasStatement = $this->database->getConnection()->prepare("DELETE FROM MailAlias WHERE address_id = :id AND target = :target");
        }


        $this->removeAliasStatement->execute(array(':id' => $this->addressId, ':target' => $address));
        unset($this->aliasList[$address]);
        $this->updateLastModified();
    }

    /**
     * Removes all targets.
     * @return void
     */
    public function clearTargets()
    {
        $this->setUp();
        if ($this->clearTargetsStm == null) {
            $this->clearTargetsStm = $this->database->getConnection()->prepare("DELETE FROM MailAlias WHERE address_id = :id");
            $this->clearTargetsStm->bindParam("id", $this->addressId);
        }
        $this->clearTargetsStm->execute();
        $this->aliasList = [];
    }

    /**
     * Will return a mailbox, if there is any. If not it will return NULL
     * @return Mailbox | null
     */
    public function getMailbox()
    {
        $this->setUp();
        return $this->mailbox;
    }

    /**
     * @return bool
     */
    public function hasMailbox()
    {
        $this->setUp();
        return $this->mailbox != null;
    }

    /**
     * Creates a new mailbox if it doesn't have one, else it returns the instance.
     * @param string $name
     * @param string $password
     * @return Mailbox
     */
    public function createMailbox($name, $password)
    {
        if (!$this->exists()) {
            return null;
        }

        if ($this->hasMailbox()) {
            return $this->mailbox;
        }

        $this->mailbox = new MailboxImpl($this->container, $this, $this->database);
        $this->mailbox->attachObserver($this);
        $this->mailbox->setName($name);
        $this->mailbox->setPassword($password);
        $this->mailbox->create();
        return $this->mailbox;
    }

    /**
     * Removes the mailbox.
     * @return void
     */
    public function deleteMailbox()
    {
        if (!$this->hasMailbox()) {
            return;
        }

        $this->mailbox->delete();
    }

    /**
     * @return AddressLibrary
     */
    public function getAddressLibrary()
    {
        return $this->addressLibrary;
    }

    /**
     * @return DomainLibrary
     */
    public function getDomainLibrary()
    {
        return $this->addressLibrary->getDomainLibrary();
    }

    public function attachObserver(Observer $observer)
    {
        $this->observerLibrary->registerObserver($observer);

    }

    public function detachObserver(Observer $observer)
    {
        $this->observerLibrary->removeObserver($observer);
    }

    private function setUp($force = false)
    {
        if ($this->hasBeenSetup && !$force) {
            return;
        }
        $this->hasBeenSetup = true;


        if (!$this->exists()) {
            return;
        }


        $row = $this->existsStatement->fetch(PDO::FETCH_ASSOC);

        if ($row['mailbox_id'] != null) {
            $this->mailbox = new MailboxImpl($this->container, $this, $this->database);
            $this->mailbox->attachObserver($this);
        }
        $this->modified = strtotime($row['modified']);
        $this->created = strtotime($row['created']);
        $this->addressId = $row['id'];
        $this->active = $row['active'] == 1;

    }

    /**
     * @return bool
     */
    private function saveAddress()
    {
        if ($this->saveStatement == null) {
            $this->saveStatement = $this->database->getConnection()->prepare("
            UPDATE MailAddress SET name = :name, active = :active, modified = NOW() WHERE id = :id");
        }


        try {
            $this->saveStatement->execute(array(
                ':name' => $this->localPart,
                ':id' => $this->addressId,
                ':active' => $this->active ? 1 : 0));


        } catch (PDOException $e) {
            return false;
        }


        $this->loadTimestamps();

        return true;
    }

    private function updateLastModified()
    {
        if ($this->updLastModStm == null) {
            $this->updLastModStm = $this->database->getConnection()->prepare("UPDATE MailAddress SET modified = NOW() WHERE id = :id");
            $this->updLastModStm->bindParam('id', $this->addressId);
        }
        $this->updLastModStm->execute();
        $this->loadTimestamps();
    }


    private function loadTimestamps()
    {

        if (!$this->exists()) {
            return;
        }


        $row = $this->existsStatement->fetch(PDO::FETCH_ASSOC);
        $this->modified = strtotime($row['modified']);
        $this->created = strtotime($row['created']);
    }

    private function setUpAlias()
    {
        if ($this->aliasList != null) {
            return;
        }

        $this->setUp();

        if ($this->setupAliasStatement == null) {
            $this->setupAliasStatement = $this->database->getConnection()->prepare("SELECT target FROM MailAlias WHERE address_id = :id ORDER BY target ASC");
            $this->setupAliasStatement->bindParam("id", $this->addressId);
        }


        $this->setupAliasStatement->execute();

        $this->aliasList = array();

        foreach ($this->setupAliasStatement->fetchAll(PDO::FETCH_NUM) as $row) {
            $this->aliasList[$row[0]] = $row[0];
        }
    }

    /**
     * @param string $target
     * @return bool
     */
    public function hasTarget($target)
    {
        $this->setUpAlias();

        return isset($this->aliasList[trim($target)]);
    }

    public function onChange(Observable $subject, $changeType)
    {

        if ($subject instanceof Mailbox) {
            if ($this->mailbox !== $subject || $changeType != Mailbox::EVENT_DELETE) {
                return;
            }
            $this->mailbox->detachObserver($this);
            $this->mailbox = null;

        } else if ($subject instanceof User) {
            if ($changeType == User::EVENT_DELETE) {
                $subject->detachObserver($this);
            } else if ($changeType == User::EVENT_USERNAME_UPDATE) {
                $userKey = array_search($subject, $this->owners);
                if ($userKey === false) {
                    return;
                }
                if ($userKey == $subject->getUsername()) {
                    return;
                }
                $this->owners[$subject->getUsername()] = $this->owners[$userKey];
                unset($this->owners[$userKey]);


            }
        }


    }

    private function callObservers($event)
    {
        $this->observerLibrary->callObservers($event);

    }

    /**
     * @return string
     */
    public function getId()
    {
        $this->setUp();
        return $this->addressId;
    }

    /**
     * Adds a user as owner of the address.
     * @param User $owner
     * @return void
     */
    public function addOwner(User $owner)
    {
        if ($this->isOwner($owner) || !$this->exists()) {
            return;
        }

        if ($this->addOwnerStatement == null) {
            $this->addOwnerStatement = $this->database->getConnection()->prepare("INSERT INTO MailAddressUserOwnership (address_id, username) VALUES (?, ?)");
        }
        $this->addOwnerStatement->execute(array($this->getId(), $owner->getUsername()));
        $this->owners[$owner->getUsername()] = $owner;
        $owner->attachObserver($this);


    }

    /**
     * Removes a user as owner of the address.
     * @param User $owner
     * @return void
     */
    public function removeOwner(User $owner)
    {

        if (!$this->isOwner($owner)) {
            return;
        }

        if ($this->removeOwnerStatement == null) {
            $this->removeOwnerStatement = $this->database->getConnection()->prepare("DELETE FROM MailAddressUserOwnership WHERE address_id = ? AND username = ?");
        }
        $this->removeOwnerStatement->execute(array($this->getId(), $owner->getUsername()));

        /** @var User $owner */
        $owner = $this->owners[$owner->getUsername()];
        $owner->detachObserver($this);
        unset($this->owners[$owner->getUsername()]);
    }

    /**
     * Checks if a user is a owner of the address.
     * @param User $owner
     * @return bool
     */
    public function isOwner(User $owner)
    {
        $this->setUpOwners();
        return isset($this->owners[$owner->getUsername()]);
    }

    /**
     * Lists the username of the owners as strings.
     * @param bool $instances If true will returns instances rather than username strings
     * @return array
     */
    public function listOwners($instances = false)
    {

        $this->setUpOwners();
        $returnArray = array();
        if ($instances) {
            foreach ($this->owners as $key => $val) {
                $returnArray[] = $this->userLibrary->getUser($key);
            }

        } else {
            foreach ($this->owners as $key => $val) {
                $returnArray[] = $key;
            }

        }

        return $returnArray;
    }

    private function setUpOwners()
    {
        if ($this->setUpOwnerStatement == null) {
            $this->setUpOwnerStatement = $this->database->getConnection()->prepare("SELECT username FROM MailAddressUserOwnership WHERE address_id = ?");
            $this->setUpOwnerStatement->execute(array($this->getId()));
            foreach ($this->setUpOwnerStatement->fetchAll(PDO::FETCH_NUM) as $row) {
                $username = $row[0];
                $this->owners[$username] = $user = $this->userLibrary->getUser($username);
                $user->attachObserver($this);
            }
        }

    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return Object
     */
    public function jsonObjectSerialize()
    {
        return new MailAddressObjectImpl($this);
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
        return $this->container->getTypeHandlerLibraryInstance()->getMailAddressTypeHandlerInstance($this);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {

        /** @var  ObserverLibrary */
        return serialize([
            $this->observerLibrary,
            $this->localPart,
            $this->database,
            $this->addressLibrary,
            $this->active,
            $this->hasBeenSetup,
            $this->domainName,
            $this->mailbox,
            $this->aliasList,
            $this->addressId,
            $this->modified,
            $this->created,
            $this->owners,
            $this->userLibrary,
            $this->container]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $array = unserialize($serialized);
        $this->observerLibrary = $array[0];
        $this->localPart = $array[1];
        $this->database = $array[2];
        $this->addressLibrary = $array[3];
        $this->active = $array[4];
        $this->hasBeenSetup = $array[5];
        $this->domainName = $array[6];
        /** @var  Mailbox */
        $this->mailbox = $array[7];
        $this->aliasList = $array[8];
        $this->addressId = $array[9];

        $this->modified = $array[10];
        $this->created = $array[11];
        $this->owners = $array[12];
        $this->userLibrary = $array[13];
        $this->container = $array[14];
    }
}