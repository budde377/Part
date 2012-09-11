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

    public function __construct(DB $database)
    {
        $this->database = $database;
        $this->connection = $database->getConnection();
        $this->initializeLibrary();
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
        $this->sites[$site->getTitle()]->delete();
        unset($this->sites[$site->getTitle()]);
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
}
