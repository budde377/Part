<?php
require_once dirname(__FILE__) . '/../_interface/Observer.php';
require_once dirname(__FILE__) . '/../_interface/SiteLibrary.php';
require_once dirname(__FILE__) . '/SiteImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/16/12
 * Time: 11:05 PM
 */
class SiteLibraryImpl implements SiteLibrary, Observer
{

    private $database;
    private $connection;

    private $sites = array();

    /** @var $sitesIterator Iterator */
    private $sitesIterator;

    public function __construct(DB $database)
    {
        $this->database = $database;
        $this->connection = $database->getConnection();
        $this->initializeLibrary();
        $this->setUpIterator();
    }

    public function initializeLibrary()
    {
        $sql = "SELECT title FROM Sites";
        $statement = $this->connection->query($sql);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $site = new SiteImpl($row['title'], $this->database);
            $site->attachObserver($this);
            $this->sites[$row['title']] = $site;
        }
    }

    /**
     * Create a array of Sites in persistent storage
     * @return array
     */
    public function listSites()
    {
        $output = array();
        foreach ($this->sites as $site) {
            $output[] = $site;
        }
        return $output;
    }

    /**
     * Create a site, this will add the site to internal list.
     * ListSites will contain same instance as returned
     * @param string $title Id must be unique else failure
     * @return Site | bool Will return a new Site or FALSE if failure
     */
    public function createSite($title)
    {
        $site = new SiteImpl($title, $this->database);
        if ($site->create()) {
            $site->attachObserver($this);
            $this->sites[$title] = $site;
            ksort($this->sites,SORT_STRING);
            $this->setUpIterator();
            return $site;
        }
        return false;
    }

    /**
     * Performs deletion of site. The deletion directly on the object should do the same.
     * @param Site $site Must be instance registered internally in Library
     * @return bool Return FALSE on failure else TRUE
     */
    public function deleteSite(Site $site)
    {
        if (!isset($this->sites[$site->getTitle()]) || $this->sites[$site->getTitle()] !== $site) {
            return false;
        }
        /** @var $site Site */
        $site = $this->sites[$site->getTitle()];
        $site->delete();
        unset($this->sites[$site->getTitle()]);
        $this->setUpIterator();
        return true;
    }

    public function onChange(Observable $subject, $changeType)
    {
        switch ($changeType) {
            case Site::EVENT_DELETE:
                if ($subject instanceof Site) {
                    /** @var $subject Site */
                    $this->deleteSite($subject);
                }
                break;
            case Site::EVENT_TITLE_UPDATE:
                if ($subject instanceof Site) {
                    /** @var $subject Site */

                    foreach ($this->sites as $key => $site) {
                        /** @var $site Site */
                        if ($site->getTitle() == $subject->getTitle()) {
                            $this->sites[$subject->getTitle()] = $site;
                            unset($this->sites[$key]);
                        }
                    }
                }
        }
    }

    /**
     * @param string $siteTitle
     * @return Site | null
     */
    public function getSite($siteTitle)
    {
        return isset($this->sites[$siteTitle]) ? $this->sites[$siteTitle]: null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return Site Can return any type.
     */
    public function current()
    {
        return $this->sitesIterator->current();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->sitesIterator->next();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->sitesIterator->key();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->sitesIterator->valid();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->sitesIterator->rewind();
    }

    private function setUpIterator()
    {
        $arrayObject = new ArrayObject($this->listSites());
        $this->sitesIterator = $arrayObject->getIterator();
    }
}
