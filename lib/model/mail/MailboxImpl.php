<?php
namespace ChristianBudde\Part\model\mail;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:02 PM
 */
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\MailMailboxObjectImpl;
use ChristianBudde\Part\util\Observer;
use ChristianBudde\Part\util\ObserverLibraryImpl;
use ChristianBudde\Part\util\traits\EncryptionTrait;
use PDO;
use PDOStatement;

class MailboxImpl implements Mailbox{


    use EncryptionTrait;

    private $observerLibrary;

    private $address;
    private $db;

    private $name = '';
    private $password;

    private $hasBeenSetup = false;
    /** @var  PDOStatement */
    private $existsStatement;
    private $deleteStatement;
    private $createStatement1;
    private $createStatement2;
    private $created = 0;
    private $modified = 0;
    private $saveChangesStatement;
    private $container;

    function __construct(BackendSingletonContainer $container, Address $address)
    {
        $this->container = $container;
        $this->observerLibrary = new ObserverLibraryImpl($this);
        $this->address = $address;
        $this->db = $container->getDBInstance();
    }


    /**
     * Sets the owners name of the mailbox
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->setUp();
        $this->name = trim($name);
        $this->saveChanges();
    }

    /**
     * @return string The name of the owner
     */
    public function getName()
    {
        $this->setUp();
        return $this->name;
    }

    /**
     * Sets the password of the mailbox
     * @param string $password
     * @return void
     */
    public function setPassword($password)
    {
        $password = trim($password);

        if($password == ""){
            return;
        }

        $this->setUp();

        $this->password = crypt($password, "$1$".$this->generateMtRandomString()."$");
        $this->saveChanges();

    }

    /**
     * Deletes the mailbox
     * @return void
     */
    public function delete()
    {
        if($this->deleteStatement == null){
            $this->deleteStatement = $this->db->getConnection()->prepare("DELETE FROM MailMailbox WHERE secondary_address_id = :id ");

        }
        $this->deleteStatement->execute(array(':id'=>$this->address->getId()));
        $this->observerLibrary->callObservers(Mailbox::EVENT_DELETE);
    }

    /**
     * @return bool
     */
    public function exists()
    {
        if($this->existsStatement == null){
            $this->existsStatement = $this->db->getConnection()->prepare("SELECT * FROM MailMailbox WHERE secondary_address_id = :id");
        }

        $this->existsStatement->execute(array(':id'=>$this->address->getId()));
        return $this->existsStatement->rowCount() > 0;
    }

    /**
     * Creates the mailbox
     * @return void
     */
    public function create()
    {
        if($this->exists()){
            return;
        }

        $uniqueAddress = $this->address->getAddressLibrary()->createAddress($id = uniqid("mail"));

        if($this->createStatement1 == null){
            $this->createStatement1 = $this->db->getConnection()->prepare("
            INSERT INTO MailMailbox (primary_address_id, secondary_address_id, password, name, created, modified, id)
            VALUES (:primary_id, :secondary_id, :password, :name, NOW(), NOW(), :id)");
            $this->createStatement2 = $this->db->getConnection()->prepare("UPDATE MailAddress SET mailbox_id = :mail_id WHERE id = :id1 OR id = :id2");
        }

        $primaryId = $uniqueAddress->getId();
        $secondaryId = $this->address->getId();

        if($this->password == null){
            $this->setPassword(uniqid('password', true));
        }


        $this->createStatement1->execute(array(
            ':primary_id'=>$primaryId,
            ':secondary_id'=>$secondaryId,
            ':password'=>$this->password,
            ':name'=>$this->name,
            ':id' => $id));

        $this->createStatement2->execute(array(
            'mail_id'=>$id,
            ':id1'=> $primaryId,
            ':id2'=>$secondaryId
        ));

        $this->setUp(true);
    }

    public function attachObserver(Observer $observer)
    {
        $this->observerLibrary->registerObserver($observer);
    }

    public function detachObserver(Observer $observer)
    {
        $this->observerLibrary->removeObserver($observer);
    }

    /**
     * Checks if the password matches the stored password.
     * @param string $password
     * @return bool
     */
    public function checkPassword($password)
    {
        $this->setUp();
        return password_verify($password, $this->password);
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return AddressLibrary
     */
    public function getAddressLibrary()
    {
        return $this->address->getAddressLibrary();
    }

    /**
     * @return Domain
     */
    public function getDomain()
    {
        return $this->address->getDomain();
    }

    /**
     * @return DomainLibrary
     */
    public function getDomainLibrary()
    {
        return $this->address->getDomainLibrary();
    }

    private function setUp($force = false)
    {

        if($this->hasBeenSetup && !$force){
            return;
        }
        $this->hasBeenSetup = true;
        if(!$this->exists()){
            return;
        }

        $row = $this->existsStatement->fetch(PDO::FETCH_ASSOC);

        $this->name = $row['name'];
        $this->modified = strtotime($row['modified']);
        $this->created = strtotime($row['created']);
        $this->password = $row['password'];

    }




    /**
     * @return int
     */
    public function lastModified()
    {
        $this->setUp();
        return $this->modified;
    }

    /**
     * @return int
     */
    public function createdAt()
    {
        $this->setUp();
        return $this->created;
    }

    private function saveChanges()
    {
        if($this->saveChangesStatement == null){
            $this->saveChangesStatement = $this->db->getConnection()->prepare("
            UPDATE MailMailbox
            SET name = :name, password = :password, modified = NOW()
            WHERE secondary_address_id = :id");
        }

        $this->saveChangesStatement->execute(array(':name'=>$this->name, ':password'=>$this->password, ':id'=>$this->address->getId()));
        $this->setUp(true);
    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return Object
     */
    public function jsonObjectSerialize()
    {
        return new MailMailboxObjectImpl($this);
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
        return $this->container->getTypeHandlerLibraryInstance()->getMailboxTypeHandlerInstance($this);
    }
}