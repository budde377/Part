<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/7/14
 * Time: 1:23 PM
 */
class MailDomainImpl implements MailDomain, Observer
{
    private $observerLibrary;
    private $addressLibrary;

    private $domain;
    /** @var  DB */
    private $db;
    private $library;
    private $database;

    private $active = true;
    private $desc = "";
    /** @var  MailDomain */
    private $aliasTarget;
    private $createdTime = 0;
    private $modifiedTime = 0;

    private $setupStatement;
    private $existsStatement;
    private $createStatement1;
    private $createStatement2;
    private $deleteStatement1;
    private $deleteStatement2;
    private $createViewsStatement;
    private $setupDomainStatement;
    private $saveChangesStatement;
    private $saveAliasChangesStatement1;
    private $saveAliasChangesStatement2;

    private $hasBeenSetup = false;
    private $aliasHasBeenSetup = false;


    function __construct($domain, $database, DB $db,  UserLibrary $userLibrary, MailDomainLibrary $library)
    {
        $this->userLibrary = $userLibrary;
        $this->observerLibrary = new ObserverLibraryImpl($this);
        $this->db = $db;
        $this->domain = $domain;
        $this->library = $library;
        $this->database = $database;
    }


    /**
     * Gets the current domain name.
     * @return string
     */
    public function getDomainName()
    {
        return $this->domain;
    }


    /**
     * Indicates if the domain is active
     * @return bool
     */
    public function isActive()
    {
        $this->setupDomain();
        return $this->active;
    }

    /**
     * @return void
     */
    public function activate()
    {
        $this->setupDomain();
        if($this->isActive()){
            return;
        }

        $this->active = true;
        $this->saveChanges();
    }

    /**
     * @return void
     */
    public function deactivate()
    {
        $this->setupDomain();
        if(!$this->isActive()){
            return;
        }
        $this->active = false;
        $this->saveChanges();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $this->setupDomain();
        return $this->desc;
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->setupDomain();
        $this->desc = $description;
        $this->saveChanges();

    }

    /**
     * The last time it was modified
     * @return int A UNIX timestamp in seconds
     */
    public function lastModified()
    {
        $this->setupDomain();
        return $this->modifiedTime;
    }

    /**
     * The creation time
     * @return int A UNIX timestamp in seconds
     */
    public function createdAt()
    {
        $this->setupDomain();
        return $this->createdTime;
    }

    /**
     * @return bool TRUE if exists else FALSE
     */
    public function exists()
    {
        if ($this->existsStatement == null) {
            $this->existsStatement = $this->db->getConnection()->prepare("SELECT * FROM MailDomain WHERE domain = :domain");
            $this->existsStatement->bindParam('domain', $this->domain);
        }

        $this->existsStatement->execute();
        return $this->existsStatement->rowCount() > 0;
    }

    /**
     * Creates the domain if it does not exist.
     * @param string $password
     * @return bool
     */
    public function create($password)
    {
        if($this->exists()){
            return true;
        }

        try{
            if ($this->createStatement1 == null) {
                $this->createStatement1 =
                    $this->db
                        ->getMailConnection($password)
                        ->prepare("INSERT INTO DomainAssignment (domain, `database`)
                    VALUES (:domain, :database)");
                $this->createStatement1->bindParam('domain', $this->domain);
                $this->createStatement1->bindParam('database', $this->database);
            }

            $this->createStatement1->execute();

            $this->createViews($password);

        } catch (PDOException $e){
            return false;
        }


        if ($this->createStatement2 == null) {
            $this->createStatement2 =
                $this->db
                    ->getConnection()
                    ->prepare("
                    INSERT INTO MailDomain (domain, description, created, modified, active)
                    VALUES (?, ?, NOW(), NOW(), ?)");

        }

        $this->createStatement2->execute(array($this->domain, $this->desc, $this->active?1:0));
        $this->setupDomain(true);
        return $this->exists();
    }

    /**
     * Deletes the domain.
     * @param string $password
     * @return bool
     */
    public function delete($password)
    {

        if(!$this->exists()){
            return true;
        }

        try{
            if ($this->deleteStatement1 == null) {
                $this->deleteStatement1 =
                    $this->db
                        ->getMailConnection($password)
                        ->prepare("DELETE FROM DomainAssignment WHERE domain=:domain");
                $this->deleteStatement1->bindParam('domain', $this->domain);
            }

            $this->deleteStatement1->execute();

            $this->createViews($password);

        } catch (PDOException $e){
            return false;
        }



        if ($this->deleteStatement2 == null) {
            $this->deleteStatement2 =
                $this->db
                    ->getConnection()
                    ->prepare("DELETE FROM MailDomain WHERE domain=:domain");
            $this->deleteStatement2->bindParam('domain', $this->domain);

        }

        $success = $this->deleteStatement2->execute();
        if($success){
            $this->callObservers();
        }
        return $success;
    }

    /**
     * @return MailAddressLibrary
     */
    public function getAddressLibrary()
    {
        return $this->addressLibrary == null? $this->addressLibrary = new MailAddressLibraryImpl($this->db, $this->userLibrary, $this):$this->addressLibrary;
    }

    /**
     * @return bool
     */
    public function isAliasDomain()
    {
        $this->setupAlias();
        return $this->aliasTarget != null;
    }

    /**
     * @param MailDomain $domain
     * @return void
     */
    public function setAliasTarget(MailDomain $domain)
    {
        $this->setupAlias();
        if(!$domain->exists()){
            return;
        }

        if(!$this->library->containsDomain($domain)){
            return;
        }

        $this->aliasTarget = $domain;
        $domain->attachObserver($this);
        $this->saveAliasChanges();

    }

    /**
     * @return MailDomain
     */
    public function getAliasTarget()
    {
        $this->setupAlias();
        return $this->aliasTarget;
    }

    /**
     * @return void
     */
    public function clearAliasTarget()
    {
        $this->setupAlias();
        if($this->aliasTarget == null){
            return;
        }
        $this->aliasTarget->detachObserver($this);
        $this->aliasTarget = null;
        $this->saveAliasChanges();


    }

    public function attachObserver(Observer $observer)
    {
        $this->observerLibrary->registerObserver($observer);
    }

    public function detachObserver(Observer $observer)
    {

        $this->observerLibrary->removeObserver($observer);
    }

    private function setupDomain($force = false)
    {
        if ($this->hasBeenSetup && !$force) {
            return true;
        }
        $this->hasBeenSetup = true;


        if ($this->setupStatement == null) {
            $this->setupStatement =
                $this->db->getConnection()->prepare("SELECT * FROM MailDomain WHERE domain = :domain");
            $this->setupStatement->bindParam('domain', $this->domain, PDO::PARAM_STR);
        }

        $this->setupStatement->execute();
        if ($this->setupStatement->rowCount() == 0) {
            return false;
        }
        $result = $this->setupStatement->fetch(PDO::FETCH_ASSOC);
        $this->active = $result['active'] == 1 ? true : false;
        $this->desc = $result['description'];
        $this->modifiedTime = strtotime($result['modified']);
        $this->createdTime = strtotime($result['created']);
        return true;
    }

    /**
     * @return MailDomainLibrary
     */
    public function getDomainLibrary()
    {
        return $this->library;
    }

    public function onChange(Observable $subject, $changeType)
    {
        if($subject !== $this->aliasTarget || $changeType != MailDomain::EVENT_DELETE){
            return;
        }
        $this->clearAliasTarget();

    }

    private function callObservers()
    {
       $this->observerLibrary->callObservers(MailDomain::EVENT_DELETE);
    }

    private function createViews($password)
    {
        if($this->createViewsStatement == null){
            $this->createViewsStatement = $this->db->getMailConnection($password)->prepare("CALL procCreateViews()");
        }
        $this->createViewsStatement->execute();
    }

    private function setupAlias($force = false)
    {
        if($this->aliasHasBeenSetup && !$force){
            return;
        }
        $this->aliasHasBeenSetup = true;

        if($this->setupDomainStatement == null){
            $this->setupDomainStatement = $this->db->getConnection()->prepare("
            SELECT target_domain
            FROM MailDomainAlias
            WHERE alias_domain = :domain");
            $this->setupDomainStatement->bindParam('domain', $this->domain);
        }
        $this->setupDomainStatement->execute();
        if($this->setupDomainStatement->rowCount() == 0){
            return;
        }

        $result = $this->setupDomainStatement->fetch(PDO::FETCH_NUM);
        $this->aliasTarget = $this->library->getDomain($result[0]);


    }

    private function saveChanges()
    {
        if(!$this->exists()){
            return;
        }
        if($this->saveChangesStatement == null){
            $this->saveChangesStatement =
                $this->db->getConnection()->prepare("UPDATE MailDomain SET active = ?, description= ?, modified = NOW() WHERE `domain`= ?");
        }

        $this->saveChangesStatement->execute(array($this->active?1:0, $this->desc, $this->domain));
        $this->setupDomain(true);

    }

    private function saveAliasChanges()
    {
        if(!$this->isAliasDomain()){
            if($this->saveAliasChangesStatement1 == null){
                $this->saveAliasChangesStatement1 = $this->db->getConnection()->prepare("DELETE FROM MailDomainAlias WHERE alias_domain = :domain");
                $this->saveAliasChangesStatement1->bindParam('domain', $this->domain);
            }

            $this->saveAliasChangesStatement1->execute();
        } else {
            if($this->saveAliasChangesStatement2 == null){
                $this->saveAliasChangesStatement2 = $this->db->getConnection()->prepare("
                INSERT INTO MailDomainAlias (alias_domain, target_domain, created, modified)
                VALUES (?,?,NOW(), NOW()) ON DUPLICATE KEY UPDATE target_domain = ?, modified = NOW()");
            }

            $this->saveAliasChangesStatement2->execute(array($this->domain, $this->aliasTarget->getDomainName(), $this->aliasTarget->getDomainName()));
        }
    }
}