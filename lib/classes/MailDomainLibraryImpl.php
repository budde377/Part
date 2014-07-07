<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/4/14
 * Time: 11:22 PM
 */

class MailDomainLibraryImpl implements MailDomainLibrary{

    private $db;
    /** @var  array */
    private $domainList;
    /** @var  PDOStatement */
    private $listDomainStatement;
    /** @var  PDO */
    private $connection;

    function __construct(DB $db)
    {
        $this->db = $db;
        $this->connection = $db->getConnection();
    }


    /**
     * List the domains in the library as an assoc array
     * @return array An array of PostfixDomain s
     */
    public function listDomains()
    {
        $this->setUpList();
        return $this->domainList;
    }


    /**
     * Will get and reuse an instance of the domain.
     * @param string $domain The domain name as a string
     * @return MailDomain
     */
    public function getDomain($domain)
    {
        $this->setUpList();
        return isset($this->domainList[$domain])?$this->domainList[$domain]:null;
    }


    /**
     * @param string $domain
     * @param string $password
     * @return MailDomain
     */
    public function createDomain($domain, $password)
    {
        // TODO: Implement createDomain() method.
    }

    /**
     * Will delete the domain, if it domain is an instance in the library.
     * @param MailDomain $domain
     * @param string $password
     * @return void
     */
    public function deleteDomain(MailDomain $domain, $password)
    {
        // TODO: Implement deleteDomain() method.
    }

    public function onChange(Observable $subject, $changeType)
    {
        // TODO: Implement onChange() method.
    }

    private function setUpList()
    {
        if($this->domainList != null){
            return;
        }

        if($this->listDomainStatement == null){
            $this->listDomainStatement =
                $this->connection->prepare("
                SELECT domain
                FROM MailDomain");
        }
        $this->listDomainStatement->execute();
        $this->domainList = array();
        foreach($this->listDomainStatement->fetchAll(PDO::FETCH_ASSOC) as $d){
            $domain = $d['domain'];
            $this->domainList[$domain] = new MailDomainImpl($domain,$this->db, $this);
        }


    }

    /**
     * Check if the instance is in the library.
     * @param MailDomain $domain
     * @return bool
     */
    public function containsDomain(MailDomain $domain)
    {
        // TODO: Implement containsDomain() method.
    }
}