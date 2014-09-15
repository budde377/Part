<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 23/04/13
 * Time: 22:47
 * To change this template use File | Settings | File Templates.
 */
namespace ChristianBudde\cbweb\test\stub;

use ChristianBudde\cbweb\model\page\Page;
use ChristianBudde\cbweb\model\page\PageOrder;
use ChristianBudde\cbweb\controller\json\JSONObject;
use ChristianBudde\cbweb\controller\json\UserPrivilegesJSONObjectImpl;

class StubUserPrivilegesImpl implements \ChristianBudde\cbweb\UserPrivileges
{
    public $pagePrivileges;
    private $root, $site, $page;

    public function __construct($root, $site, $page)
    {
        $this->root = $root;
        $this->site = $site;
        $this->page = $page;
    }

    /**
     * Will add root privileges
     * @return void
     */
    public function addRootPrivileges()
    {

    }

    /**
     * Will add Site privileges
     * @return void
     */
    public function addSitePrivileges()
    {

    }

    /**
     * Will add privileges to given page
     * @param Page $page
     * @return void
     */
    public function addPagePrivileges(Page $page)
    {

    }

    /**
     * @return bool Return TRUE if has privilege else FALSE
     */
    public function hasRootPrivileges()
    {
        return $this->root;
    }

    /**
     * @return bool Return TRUE if has privilege else FALSE
     */
    public function hasSitePrivileges()
    {
        return $this->site;
    }

    /**
     * @param Page $page
     * @return bool Return TRUE if has privilege else FALSE
     */
    public function hasPagePrivileges(Page $page)
    {
        return $this->page;
    }

    /**
     * Will revoke Root privileges
     * @return void
     */
    public function revokeRootPrivileges()
    {

    }

    /**
     * Will revoke Site privileges
     * @return void
     */
    public function revokeSitePrivileges()
    {

    }

    /**
     * Will revoke privileges from given Page
     * @param \ChristianBudde\cbweb\model\page\Page $page
     * @return void
     */
    public function revokePagePrivileges(Page $page)
    {

    }

    /**
     * This will revoke all privileges
     * @return void
     */
    public function revokeAllPrivileges()
    {

    }

    /**
     * Will return an array of strings containing the sites that are under the users control.
     * If the user has site or root privileges an empty array is returned.
     * If the user has no privileges an empty array is returned.
     *
     * @param \ChristianBudde\cbweb\model\page\PageOrder $pageOrder If order is given it will return array containing instances from the PageOrder
     * @return array
     */
    public function listPagePrivileges(PageOrder $pageOrder = null)
    {
        return $this->pagePrivileges;
    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return \ChristianBudde\cbweb\ajax\json\\ChristianBudde\cbweb\controller\ajax\json\JSONObject
     */
    public function jsonObjectSerialize()
    {
        return new UserPrivilegesJSONObjectImpl($this);
    }


    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->jsonObjectSerialize()->jsonSerialize();
    }
}