<?php
require_once dirname(__FILE__) . '/../../_interface/SiteLibrary.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/18/12
 * Time: 1:16 AM
 */
class StubSiteLibraryImpl implements SiteLibrary
{

    private $list;

    /**
     * Create a array of Sites in persistent storage
     * @return array
     */
    public function listSites()
    {
        return $this->list;
    }

    /**
     * Create a site, this will add the site to internal list.
     * ListSites will contain same instance as returned
     * @param string $title Id must be unique else failure
     * @return Site | bool Will return a new Site or FALSE if failure
     */
    public function createSite($title)
    {
        return false;
    }

    /**
     * Performs deletion of site. The deletion directly on the object should do the same.
     * @param Site $site Must be instance registered internally in Library
     * @return bool Return FALSE on failure else TRUE
     */
    public function deleteSite(Site $site)
    {
        return false;
    }

    /**
     * @param array $list
     */
    public function setSiteList($list)
    {
        $this->list = $list;
    }

    /**
     * @param string $siteTitle
     * @return Site | null
     */
    public function getSite($siteTitle)
    {
        foreach($this->list as $site){
            /** @var $site Site */
            if($site->getTitle() == $siteTitle){
                return $site;
            }
        }
        return null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
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
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
    }
}
