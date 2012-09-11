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
}
