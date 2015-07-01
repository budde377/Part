<?php
namespace ChristianBudde\Part\test\stub;

use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\PageOrderObjectImpl;
use ChristianBudde\Part\model\page\Page;
use ChristianBudde\Part\model\page\PageOrder;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/20/12
 * Time: 1:24 PM
 */
class StubPageOrderImpl implements PageOrder
{

    private $order;
    private $inactiveList = array();
    private $pagePath;

    /**
     * This will return pageOrder. If null is given, it will return top-level
     * order, else if valid page id is given, it will return the order of the
     * sub-list. The return array will, if non-empty, contain instances of Page
     * If invalid id is provided, it will return empty array
     * @param null|\ChristianBudde\Part\model\page\Page $parentPage
     * @return array
     */
    public function getPageOrder(Page $parentPage = null)
    {
        $id = null;
        if ($parentPage instanceof Page) {
            $id = $parentPage->getID();
        }
        return isset($this->order[$id]) && !empty($this->order[$id]) &&
        is_array($this->order[$id]) ? $this->order[$id] : array();
    }

    /**
     * This will set the pageOrder of given page ID.
     * There must not be created loops and parent/id must be valid page ID (and existing),
     * else the function will fail and return FALSE. If proper id('s) and no loops created,
     * function will return TRUE
     * @param \ChristianBudde\Part\model\page\Page $page
     * @param int $place
     * @param null | \ChristianBudde\Part\model\page\Page $parentPage
     * @return bool
     */
    public function setPageOrder(Page $page, $place = PageOrder::PAGE_ORDER_LAST, Page $parentPage = null)
    {
        return false;
    }

    /**
     * Will return TRUE if the page is active (ie. in order), else FALSE
     * A page is only active if it is attached to root node (null)
     * @param \ChristianBudde\Part\model\page\Page $page
     * @return bool
     */
    public function isActive(Page $page)
    {
        return false;
    }

    /**
     * Will list all pages in an array as instances of Page
     * @param int $listMode Must be of ListPageEnum
     * @return array
     */
    public function listPages($listMode = PageOrder::LIST_ALL)
    {
        $returnArray = array();
        if ($listMode == PageOrder::LIST_ACTIVE || $listMode == PageOrder::LIST_ALL) {
            $this->createPageList($returnArray);
        }
        if ($listMode == PageOrder::LIST_INACTIVE || $listMode == PageOrder::LIST_ALL) {
            foreach ($this->inactiveList as $inactivePage) {
                $returnArray[] = $inactivePage;
            }
        }
        return $returnArray;
    }



    /**
     * This will delete a page from page order and in general
     * @param \ChristianBudde\Part\model\page\Page $page
     * @return bool
     */
    public function deletePage(Page $page)
    {
        return false;
    }

    /**
     * Will deactivate a page and all it's sub pages.
     * The page order remains the same
     * @param \ChristianBudde\Part\model\page\Page $page
     * @return void
     */
    public function deactivatePage(Page $page)
    {

    }

    public function setOrder($order)
    {
        $this->order = $order;
    }


    private function createPageList(&$array, Page $parentPage = null)
    {
        $list = $this->getPageOrder($parentPage);
        foreach ($list as $page) {
            $array[] = $page;
            $this->createPageList($array, $page);
        }
    }

    /**
     * @param array $array
     */
    public function setInactiveList($array)
    {
        $this->inactiveList = $array;
    }

    /**
     * @param string $id
     * @return Page | null Page if Page with title is found, else null
     */
    public function getPage($id)
    {
        foreach ($this->listPages(PageOrder::LIST_ALL) as $page) {
            /** @var $page \ChristianBudde\Part\model\page\Page */
            if ($page->getID() == $id) {
                return $page;
            }
        }
        return null;
    }

    /**
     * Will return the path of an page as an array.
     * If the page is at top level an array containing an single entrance will be returned
     * Else a numeric array with the top level as first entrance, and lowest level as last entrance
     * will be returned.
     * If a page is inactive, an empty array will be returned.
     * If a page is not found, FALSE will be returned.
     * @param \ChristianBudde\Part\model\page\Page $page
     * @return bool | array
     */
    public function getPagePath(Page $page)
    {
        return $this->pagePath[$page->getID()];
    }

    public function setPagePath(array $pagePath)
    {
        $this->pagePath = $pagePath;
    }


    /**
     * Will return the current page from the current page
     * strategy.
     * @return \ChristianBudde\Part\model\page\Page
     */
    public function getCurrentPage()
    {

    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return Object
     */
    public function jsonObjectSerialize()
    {
        return new PageOrderObjectImpl($this);
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

    /**
     * @return TypeHandler
     */
    public function generateTypeHandler()
    {
    }

    /**
     * @param string $id must satisfy syntax of Page id
     * @param string $title
     * @param string $template
     * @param string $alias
     * @param bool $hidden
     * @return bool|Page Returns FALSE on invalid id or other error, else instance of Page
     */
    public function createPage($id, $title = '', $template = '', $alias = '', $hidden = false)
    {
        return false;
    }
}
