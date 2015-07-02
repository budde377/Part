<?php
namespace ChristianBudde\Part\model\page;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\PageOrderObjectImpl;
use ChristianBudde\Part\exception\MalformedParameterException;
use PDO;
use PDOException;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/16/12
 * Time: 9:13 PM
 * To change this template use File | Settings | File Templates.
 */
class PageOrderImpl implements PageOrder
{

    private $database;

    private $inactivePages = [];
    private $activePages = [];
    private $pageOrder = [];

    private $container;


    public function __construct(BackendSingletonContainer $container)
    {
        $this->database = $container->getDBInstance();
        $this->container = $container;
        $this->initializeInactivePageOrder();
        $this->initializeActivePageOrder();
    }


    private function initializeActivePageOrder()
    {
        $statement = $this->container->getDBInstance()->getConnection()
            ->query("
SELECT Page.page_id, Page.title, Page.template, Page.alias,UNIX_TIMESTAMP(Page.last_modified) as last_modified, Page.hidden,PageOrder.parent_id
FROM Page INNER JOIN PageOrder ON Page.page_id = PageOrder.page_id
ORDER BY parent_id,order_no");
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $page = $this->createPageInstance($row['page_id'], $row['title'], $row['template'], $row['alias'], $row['last_modified'], $row['hidden']);
            $this->activePages[$row['page_id']] = $page;
            $this->pageOrder[$row['parent_id']][] = $row['page_id'];
        }

    }

    private function initializeInactivePageOrder()
    {

        $statement = $this->container->getDBInstance()->getConnection()
            ->query("SELECT *,UNIX_TIMESTAMP(Page.last_modified) as last_modified FROM Page WHERE Page.page_id NOT IN (SELECT page_id FROM PageOrder)");
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $page = $this->createPageInstance($row['page_id'], $row['title'], $row['template'], $row['alias'], $row['last_modified'], $row['hidden']);
            $this->inactivePages[$row['page_id']] = $page;
        }

    }


    private function createPageInstance($id, $title, $template, $alias, $lastMod, $hidden)
    {
        $page = new PageImpl($this->container, $this, $id, $title, $template, $alias, $lastMod, $hidden);
        return $page;

    }

    /**
     * This will return pageOrder. If null is given, it will return top-level
     * order, else if valid page id is given, it will return the order of the
     * sub-list. The return array will, if non-empty, contain instances of Page
     * If invalid id is provided, it will return empty array
     * @param null|Page $parentPage
     * @throws MalformedParameterException
     * @return array
     */
    public function getPageOrder(Page $parentPage = null)
    {
        if ($parentPage instanceof Page) {
            $parentPageString = $parentPage->getID();
        } else {
            $parentPageString = '';
        }
        if (!isset($this->pageOrder[$parentPageString])) {
            return [];
        }

        $retArray = [];
        foreach ($this->pageOrder[$parentPageString] as $id) {
            $retArray[] = $this->activePages[$id];
        }

        return $retArray;
    }

    /**
     * This will set the pageOrder of given Page.
     * There must not be created loops and parent/id must be valid Page (and existing),
     * else the function will fail and return FALSE. If proper id('s) and no loops created,
     * function will return TRUE
     * @param Page $page
     * @param int $place
     * @param null | Page $parentPage
     * @throws \ChristianBudde\Part\exception\MalformedParameterException
     * @return bool
     */
    public function setPageOrder(Page $page, $place = PageOrder::PAGE_ORDER_LAST, Page $parentPage = null)
    {

        if ($parentPage instanceof Page) {
            if ($this->findPage($parentPage) !== false) {
                $parentPageId = $parentPage->getID();
            } else {
                return false;
            }
        } else {
            $parentPageId = '';
        }
        if(!isset($this->pageOrder[$parentPageId])){
            $this->pageOrder[$parentPageId] = [];
        }

        $findPage = $this->findPage($page);
        if ($findPage === false || $this->detectLoop($page->getID(), $parentPageId)) {
            return false;
        }

        if ($findPage == 'active') {
            $this->removeIDFromSubLists($page->getID());
        } else {
            $this->activatePageId($page->getID());
        }
        $this->container->getDBInstance()->getConnection()->beginTransaction();
        $this->insertPageID($page->getID(), $place, $parentPageId);
        $this->container->getDBInstance()->getConnection()->commit();
        return true;
    }

    /**
     * Will return TRUE if the page is active (ie. in order), else FALSE
     * @param Page $page
     * @return bool
     */
    public function isActive(Page $page)
    {
        return $this->findPage($page) == 'active';
    }

    /**
     * Will list all pages in an array as instances of Page
     * @param int $listMode Must be of ListPageEnum
     * @return array
     */
    public function listPages($listMode = PageOrder::LIST_ALL)
    {
        $retArray = array();
        if ($listMode == PageOrder::LIST_INACTIVE || $listMode == PageOrder::LIST_ALL) {
            foreach ($this->inactivePages as $page) {
                $retArray[] = $page;
            }
        }
        if ($listMode == PageOrder::LIST_ALL || $listMode == PageOrder::LIST_ACTIVE) {
            foreach ($this->activePages as $page) {
                $retArray[] = $page;
            }
        }

        return $retArray;

    }

    /**
     * @param string $id must satisfy syntax of Page id
     * @param string $title
     * @param string $template
     * @param string $alias
     * @param bool $hidden
     * @return bool|Page Returns FALSE on invalid id or other error, else instance of Page
     */
    public function createPage($id, $title='', $template = '', $alias = '', $hidden = false)
    {
        if($this->getPage($id) != null){
            return false;
        }

        try{
            $page = new PageImpl($this->container, $this, $id,$title, $template, $alias, 0, $hidden);
        } catch (MalformedParameterException $e){
            return false;
        }
        $createStm = $this->container->getDBInstance()->getConnection()->prepare("
            INSERT INTO Page (page_id,template,title,alias,hidden)
            VALUES (:page_id,:template,:title,:alias,:hidden)");
        try {
            $createStm->execute(['page_id'=>$id, 'template'=>$template, 'title'=>$title, 'alias'=>$alias, 'hidden'=>$hidden]);
        } catch (PDOException $e) {
            return false;
        }

        if ($createStm->rowCount() == 0) {
            return false;
        }
        $this->inactivePages[$id] = $page;
        return $page;

    }

    /**
     * Will deactivate a page and all it's sub pages.
     * The page order remains the same
     * @param Page $page
     * @return void
     */
    public function deactivatePage(Page $page)
    {

        if (!$this->isActive($page)) {
            return;
        }
        $this->container->getDBInstance()->getConnection()->prepare("DELETE FROM PageOrder WHERE page_id = :id OR parent_id = :id")->execute(['id'=>$page->getID()]);
        $this->removeIDFromSubLists($page->getID());
        $this->deactivatePageId($page->getID());

        if (!isset($this->pageOrder[$page->getID()])) {
            return;
        }
        foreach ($this->pageOrder[$page->getID()] as $page_id) {
            $this->deactivatePageId($page_id);
        }
        unset($this->pageOrder[$page->getID()]);

    }

    private function deactivatePageId($page_id){
        $this->inactivePages[$page_id] = $this->activePages[$page_id];
        unset($this->activePages[$page_id]);
    }


    private function activatePageId($page_id){
        $this->activePages[$page_id] = $this->inactivePages[$page_id];
        unset($this->inactivePages[$page_id]);
    }

    /**
     * This will delete a page from page order and in general
     * @param Page $page
     * @return bool
     */
    public function deletePage(Page $page)
    {
        $findPage = $this->findPage($page);
        if ($findPage === false) {
            return false;
        }

        $deleteStm = $this->container->getDBInstance()->getConnection()->prepare("DELETE FROM Page WHERE page_id=?");
        $deleteStm->execute([$page->getID()]);
        if (!$deleteStm->rowCount() > 0) {
            return false;
        }
        if ($findPage == 'active') {
            unset($this->activePages[$page->getID()]);
        } else {
            unset($this->inactivePages[$page->getID()]);
        }

        return true;
    }

    /**
     * @param Page $page
     * @return string | bool Will return active or inactive if found else FALSE
     */
    private function findPage(Page $page)
    {
        if (array_search($page, $this->activePages) !== false) {
            return 'active';
        }
        if (array_search($page, $this->inactivePages) !== false) {
            return 'inactive';
        }
        return false;
    }

    private function detectLoop($childID, $parentID)
    {

        $prevID = $parentID;
        $loopDetected = false;
        while (!empty($prevID) && !$loopDetected) {
            if ($prevID == $childID) {
                $loopDetected = true;
            }
            foreach ($this->pageOrder as $parent_id => $orderArray) {
                foreach ($orderArray as $page_id) {
                    if ($page_id == $prevID) {
                        $prevID = $parent_id;
                    }

                }
            }
        }

        return $loopDetected;
    }

    private function insertPageID($pageId, $place, $parentId)
    {

        $order = $this->pageOrder[$parentId];

        if($place == PageOrder::PAGE_ORDER_LAST){
            $place = count($order);
        } else {
            $place = min($place, count($order));
            $place = max(0, $place);
        }


        if ($place < count($order)) {
            $this->insertPageID($order[$place], $place + 1, $parentId);
        }
        if (empty($parentId)) {
            $stm = $this->container->getDBInstance()->getConnection()->prepare("INSERT INTO PageOrder (page_id, order_no, parent_id) VALUES (:page_id, :order_no, NULL) ON DUPLICATE KEY UPDATE order_no = :order_no, parent_id = NULL");
            $stm->execute(['page_id' => $pageId, 'order_no' => $place]);
        } else {
            $stm = $this->container->getDBInstance()->getConnection()->prepare("INSERT INTO PageOrder (page_id, order_no, parent_id) VALUES (:page_id, :order_no, :parent_id) ON DUPLICATE KEY UPDATE order_no = :order_no, parent_id = :parent_id ");
            $stm->execute(['page_id' => $pageId, 'order_no' => $place, 'parent_id' => $parentId]);
        }
        $this->pageOrder[$parentId][$place] = $pageId;

    }


    private function removeIDFromSubLists($pageID)
    {
        foreach ($this->pageOrder as $parent_id => $orderArray) {
            foreach ($orderArray as $order => $page_id) {
                if ($page_id == $pageID) {
                    unset($this->pageOrder[$parent_id][$order]);
                    $this->pageOrder[$parent_id] = array_values($this->pageOrder[$parent_id]);
                }
            }
        }
    }

    /**
     * @param string $id
     * @return Page | null Page if Page with title is found, else null
     */
    public function getPage($id)
    {
        return isset($this->activePages[$id]) ? $this->activePages[$id] : (isset($this->inactivePages[$id]) ? $this->inactivePages[$id] : null);
    }

    /**
     * Will return the path of an page as an array.
     * If the page is at top level an array containing an single entrance will be returned
     * Else a numeric array with the top level as first entrance, and lowest level as last entrance
     * will be returned.
     * If a page is inactive, an empty array will be returned.
     * If a page is not found, FALSE will be returned.
     * @param Page $page
     * @return bool | array
     */
    public function getPagePath(Page $page)
    {
        if (($r = $this->findPage($page)) == 'inactive') {
            return array();
        } else if ($r == false) {
            return false;
        }

        return $this->recursiveCalculatePath($page);
    }

    private function recursiveCalculatePath(Page $page, Page $parent = null)
    {
        $order = $this->getPageOrder($parent);
        foreach ($order as $p) {
            /** @var $p Page */
            if ($p === $page) {
                return array($p);
            } else if (($ret = $this->recursiveCalculatePath($page, $p)) !== false) {
                return array_merge(array($p), $ret);
            }
        }
        return false;
    }

    /**
     * Will return the current page from the current page
     * strategy.
     * @return Page
     */
    public function getCurrentPage()
    {
        return $this->container->getCurrentPageStrategyInstance()->getCurrentPage();
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
        return $this->container->getTypeHandlerLibraryInstance()->getPageOrderTypeHandlerInstance($this);

    }

    public function changeId(Page $page, $page_id)
    {
        if (($status = $this->findPage($page)) === false) {
            return false;
        }
        if ($status == 'inactive') {
            $this->inactivePages[$page_id] = $this->inactivePages[$page->getID()];
            unset($this->inactivePages[$page->getID()]);
        } else {
            $this->updatePageOrderId($page->getID(), $page_id);
            $this->activePages[$page_id] = $this->activePages[$page->getID()];
            unset($this->activePages[$page->getID()]);
        }
        $updateIDStm = $this->container->getDBInstance()->getConnection()->prepare("UPDATE Page SET page_id = ? WHERE page_id = ?");
        $updateIDStm->execute([$page_id, $page->getID()]);

        return true;

    }

    private function updatePageOrderId($oldId, $newId)
    {
        if (isset($this->pageOrder[$oldId])) {
            $this->pageOrder[$newId] = $this->pageOrder[$oldId];
            unset($this->pageOrder[$oldId]);
        }
        foreach($this->pageOrder as $keyId=>$order){
            foreach($order as $key=>$id){
                if($id == $oldId){
                    $this->pageOrder[$keyId][$key] = $newId;
                    return;
                }
            }
        }
    }
}
