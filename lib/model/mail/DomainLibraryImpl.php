<?php
namespace ChristianBudde\Part\model\mail;

use ChristianBudde\Part\Config;
use ChristianBudde\Part\controller\ajax\TypeHandler;
use ChristianBudde\Part\controller\json\MailDomainLibraryObjectImpl;
use ChristianBudde\Part\model\user\UserLibrary;
use ChristianBudde\Part\util\db\DB;
use ChristianBudde\Part\util\Observable;
use ChristianBudde\Part\util\Observer;
use PDO;
use PDOStatement;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/4/14
 * Time: 11:22 PM
 */
class DomainLibraryImpl implements DomainLibrary, Observer
{


    private $databaseName;
    private $db;
    /** @var  array */
    private $domainList;
    /** @var  PDOStatement */
    private $listDomainStatement;
    /** @var  PDO */
    private $connection;
    private $userLibrary;

    function __construct(Config $config, DB $db, UserLibrary $userLibrary)
    {
        $this->userLibrary = $userLibrary;
        $this->databaseName = $config->getMySQLConnection()['database'];
        $this->db = $db;
        $this->connection = $db->getConnection();
    }


    /**
     * List the domains in the library as an assoc array
     * @return Domain[] An array of PostfixDomain s
     */
    public function listDomains()
    {
        $this->setUpList();
        return $this->domainList;
    }


    /**
     * Will get and reuse an instance of the domain.
     * @param string $domain The domain name as a string
     * @return Domain | null
     */
    public function getDomain($domain)
    {
        $this->setUpList();
        return isset($this->domainList[$domain]) ? $this->domainList[$domain] : null;
    }


    /**
     * @param string $domain
     * @param string $password
     * @return Domain
     */
    public function createDomain($domain, $password)
    {
        $d = $this->getDomain($domain);
        if ($d == null) {
            $d = new DomainImpl($domain, $this->databaseName, $this->db, $this->userLibrary, $this);

            if($d->create($password)){
                $this->domainList[$domain] =$d;
                $d->attachObserver($this);
                return $d;
            }

        } else {
            if($d->create($password)){
                return $d;
            }
        }


        return null;
    }

    /**
     * Will delete the domain, if it domain is an instance in the library.
     * @param Domain $domain
     * @param string $password
     * @return void
     */
    public function deleteDomain(Domain $domain, $password)
    {
        if (!$this->containsDomain($domain)) {
            return;
        }

        if (!$domain->delete($password)) {
            return;
        }


    }

    public function onChange(Observable $subject, $changeType)
    {
        if(!($subject instanceof DomainImpl) || !$this->containsDomain($subject) || $changeType != Domain::EVENT_DELETE){
            return;
        }

        unset($this->domainList[$subject->getDomainName()]);
        $subject->detachObserver($this);

    }

    private function setUpList()
    {
        if ($this->domainList != null) {
            return;
        }

        if ($this->listDomainStatement == null) {
            $this->listDomainStatement =
                $this->connection->prepare("
                SELECT domain
                FROM MailDomain");
        }
        $this->listDomainStatement->execute();
        $this->domainList = array();
        foreach ($this->listDomainStatement->fetchAll(PDO::FETCH_ASSOC) as $d) {
            $domain = $d['domain'];
            $d = ($this->domainList[$domain] = new DomainImpl($domain, $this->databaseName, $this->db, $this->userLibrary, $this));
            $d->attachObserver($this);
        }


    }

    /**
     * Check if the instance is in the library.
     * @param Domain $domain
     * @return bool
     */
    public function containsDomain(Domain $domain)
    {
        $this->setUpList();
        return isset($this->domainList[$domain->getDomainName()]) && $this->domainList[$domain->getDomainName()] === $domain;
    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return Object
     */
    public function jsonObjectSerialize()
    {
        return new MailDomainLibraryObjectImpl($this);
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
        // TODO: Implement generateTypeHandler() method.
    }
}