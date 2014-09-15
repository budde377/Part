<?php
namespace ChristianBudde\cbweb\model\mail;

use ChristianBudde\cbweb\Config;
use ChristianBudde\cbweb\util\db\DB;
use ChristianBudde\cbweb\util\Observable;
use ChristianBudde\cbweb\util\Observer;
use PDOStatement;
use PDO;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/4/14
 * Time: 11:22 PM
 */
class MailDomainLibraryImpl implements MailDomainLibrary, Observer
{


    private $databaseName;
    private $db;
    /** @var  array */
    private $domainList;
    /** @var  PDOStatement */
    private $listDomainStatement;
    /** @var  PDO */
    private $connection;

    function __construct(Config $config, DB $db)
    {
        $this->databaseName = $config->getMySQLConnection()['database'];
        $this->db = $db;
        $this->connection = $db->getConnection();
    }


    /**
     * List the domains in the library as an assoc array
     * @return MailDomain[] An array of PostfixDomain s
     */
    public function listDomains()
    {
        $this->setUpList();
        return $this->domainList;
    }


    /**
     * Will get and reuse an instance of the domain.
     * @param string $domain The domain name as a string
     * @return MailDomain | null
     */
    public function getDomain($domain)
    {
        $this->setUpList();
        return isset($this->domainList[$domain]) ? $this->domainList[$domain] : null;
    }


    /**
     * @param string $domain
     * @param string $password
     * @return MailDomain
     */
    public function createDomain($domain, $password)
    {
        $d = $this->getDomain($domain);
        if ($d == null) {
            $d = ($this->domainList[$domain] = new MailDomainImpl($domain, $this->databaseName, $this->db, $this));
            $d->attachObserver($this);
        }

        $d->create($password);

        return $d;
    }

    /**
     * Will delete the domain, if it domain is an instance in the library.
     * @param MailDomain $domain
     * @param string $password
     * @return void
     */
    public function deleteDomain(MailDomain $domain, $password)
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
        if(!($subject instanceof MailDomainImpl) || !$this->containsDomain($subject) || $changeType != MailDomain::EVENT_DELETE){
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
            $d = ($this->domainList[$domain] = new MailDomainImpl($domain, $this->databaseName, $this->db, $this));
            $d->attachObserver($this);
        }


    }

    /**
     * Check if the instance is in the library.
     * @param MailDomain $domain
     * @return bool
     */
    public function containsDomain(MailDomain $domain)
    {
        $this->setUpList();
        return isset($this->domainList[$domain->getDomainName()]) && $this->domainList[$domain->getDomainName()] === $domain;
    }
}