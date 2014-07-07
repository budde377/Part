<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/7/14
 * Time: 1:23 PM
 */

class MailDomainImpl implements MailDomain{

    private $domain;
    /** @var  DB */
    private $db;
    private $library;

    private $active = true;
    private $desc;
    private $aliasTarget;

    private $setupStatement = false;
    private $hasBeenSetup;

    function __construct($domain, DB $db, MailDomainLibrary $library)
    {
        $this->db = $db;
        $this->domain = $domain;
        $this->library = $library;
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
        $this->active = true;
    }

    /**
     * @return void
     */
    public function deactivate()
    {
        $this->setupDomain();
        $this->active = false;
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
    }

    /**
     * The last time it was modified
     * @return int A UNIX timestamp in seconds
     */
    public function lastModified()
    {
        // TODO: Implement lastModified() method.
    }

    /**
     * The creation time
     * @return int A UNIX timestamp in seconds
     */
    public function createdAt()
    {
        // TODO: Implement createdAt() method.
    }

    /**
     * @return bool TRUE if exists else FALSE
     */
    public function exists()
    {
        // TODO: Implement exists() method.
    }

    /**
     * Creates the domain if it does not exist.
     * @param string $password
     * @return mixed
     */
    public function create($password)
    {
        // TODO: Implement create() method.
    }

    /**
     * Deletes the domain.
     * @param string $password
     * @return void
     */
    public function delete($password)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @return MailAddressLibrary
     */
    public function getAddressLibrary()
    {
        // TODO: Implement getAddressLibrary() method.
    }

    /**
     * @return bool
     */
    public function isAliasDomain()
    {
        // TODO: Implement isAliasDomain() method.
    }

    /**
     * @param MailDomain $domain
     * @return void
     */
    public function setAliasTarget(MailDomain $domain)
    {
        // TODO: Implement setAliasTarget() method.
    }

    /**
     * @return MailDomain
     */
    public function getAliasTarget()
    {
        // TODO: Implement getAliasTarget() method.
    }

    /**
     * @return void
     */
    public function clearAliasTarget()
    {
        // TODO: Implement clearAliasTarget() method.
    }

    public function attachObserver(Observer $observer)
    {
        // TODO: Implement attachObserver() method.
    }

    public function detachObserver(Observer $observer)
    {
        // TODO: Implement detachObserver() method.
    }

    private function setupDomain()
    {
        if($this->hasBeenSetup){
            return;
        }
        $this->hasBeenSetup = true;


        if($this->setupStatement == null){
            $this->setupStatement =
                $this->db->getConnection()->prepare("SELECT * FROM MailDomain WHERE domain = :domain");
            $this->setupStatement->bindParam('domain', $this->domain, PDO::PARAM_STR);
        }

        $this->setupStatement->execute();
        $result = $this->setupStatement->fetch(PDO::FETCH_ASSOC);
        $this->active = $result['active'] == 1?true:false;
        $this->desc = $result['description'];
    }
}