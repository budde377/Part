<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/16/12
 * Time: 11:05 PM
 */
interface SiteLibrary
{
    /**
     * @abstract
     * Create a array of Sites in persistent storage
     * @return array
     */
    public function listSites();

    /**
     * @abstract
     * Create a site, this will add the site to internal list.
     * ListSites will contain same instance as returned
     * @param string $title Id must be unique else failure
     * @return Site | bool Will return a new Site or FALSE if failure
     */
    public function createSite($title);

    /**
     * @abstract
     * Performs deletion of site. The deletion directly on the object should do the same.
     * @param Site $site Must be instance registered internally in Library
     * @return bool Return FALSE on failure else TRUE
     */
    public function deleteSite(Site $site);

    /**
     * @abstract
     * @param string $siteTitle
     * @return Site | null
     */
    public function getSite($siteTitle);

}
