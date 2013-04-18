<?php
require_once dirname(__FILE__) . '/../_interface/PageOrder.php';
require_once dirname(__FILE__) . '/../_interface/Observer.php';
require_once dirname(__FILE__) . '/../_class/PageImpl.php';
require_once dirname(__FILE__) . '/../_exception/MalformedParameterException.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/16/12
 * Time: 9:13 PM
 * To change this template use File | Settings | File Templates.
 */
class PageOrderImpl implements PageOrder, Observer
{

    private $database;
    private $connection;

    private $inactivePages = array();
    private $activePages = array();
    private $pageOrder = array();

    /** @var PDOStatement */
    private $deactivatePageStatement;


    public function __construct(DB $database)
    {
        $this->database = $database;
        $this->connection = $database->getConnection();

        $this->initializePageOrder();
    }

    private function initializePageOrder()
    {
        $sql = "SELECT page_id FROM Page WHERE Page.page_id NOT IN (SELECT page_id FROM PageOrder)";
        $statement = $this->connection->query($sql);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $page = new PageImpl($row['page_id'], $this->database);
            $page->attachObserver($this);
            $this->inactivePages[$row['page_id']] = $page;
        }

        $sql = "SELECT * FROM PageOrder ORDER BY parent_id,order_no";
        $statement = $this->connection->query($sql);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $page = new PageImpl($row['page_id'], $this->database);
            $page->attachObserver($this);
            $this->activePages[$row['page_id']] = $page;
            $this->pageOrder[$row['parent_id']][$row['order_no']] = $row['page_id'];
        }
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
            $parentPageString = null;
        }
        $retArray = array();
        if (!isset($this->pageOrder[$parentPageString]) || !is_array($this->pageOrder[$parentPageString])) {
            return $retArray;
        }
        ksort($this->pageOrder[$parentPageString]);
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
     * @throws MalformedParameterException
     * @return bool
     */
    public function setPageOrder(Page $page, $place = PageOrder::PAGE_ORDER_LAST, Page $parentPage = null)
    {

        if ($parentPage instanceof Page) {
            if ($this->findPage($parentPage) !== false) {
                $parentPageID = $parentPage->getID();

            } else {
                return false;
            }
        } else {
            $parentPageID = null;
        }

        $findPage = $this->findPage($page);
        if ($findPage === false || $this->detectLoop($page->getID(), $parentPageID)) {
            return false;
        }


        if ($findPage == 'inactive') {
            $this->activePages[$page->getID()] = $this->inactivePages[$page->getID()];
            unset($this->inactivePages[$page->getID()]);
        } else {
            $this->removeIDFromSubLists($page->getID());

        }

        $this->insertPageID($page->getID(), $place, $parentPageID);

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
     * @return bool | Page Returns FALSE on invalid id or other error, else instance of Page
     */
    public function createPage($id)
    {
        try {
            $page = new PageImpl($id, $this->database);

        } catch (Exception $e) {
            return false;
        }

        if ($page->create()) {
            $this->inactivePages[$id] = $page;
            $page->attachObserver($this);
            return $page;
        }
        return false;

    }

    /**
     * Will deactivate a page and all it's sub pages.
     * The page order remains the same
     * @param Page $page
     * @return void
     */
    public function deactivatePage(Page $page)
    {
        if($this->deactivatePageStatement == null){
            $this->deactivatePageStatement = $this->connection->prepare("DELETE FROM PageOrder WHERE page_id = ?");
        }

        if ($this->findPage($page) == 'active') {
            foreach($this->getPageOrder($page) as $p){
                $this->deactivatePage($p);
            }
            $this->inactivePages[$page->getID()] = $this->activePages[$page->getID()];
            unset($this->activePages[$page->getID()]);
            $this->removeIDFromSubLists($page->getID());
            $this->deactivatePageStatement->execute(array($page->getID()));
        }
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

        $deleteRet = $page->delete();
        if (!$deleteRet) {
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

    public function onChange(Observable $subject, $changeType)
    {

        if ($subject instanceof Page && ($findPage = $this->findPage($subject)) !== false) {
            /** @var $subject Page */
            switch ($changeType) {
                case Page::EVENT_DELETE:
                    if (!$subject->exists()) {
                        if ($findPage == 'active') {
                            unset($this->activePages[$subject->getID()]);
                        } else {
                            unset($this->inactivePages[$subject->getID()]);
                        }
                    }
                    break;
                case Page::EVENT_ID_UPDATE:
                    if ($findPage == 'active') {
                        $key = $this->updateKey($subject, $this->activePages);
                        $newKey = $subject->getID();
                        $pageOrderCopy = $this->pageOrder;
                        foreach ($pageOrderCopy as $parent_id => $orderArray) {
                            foreach ($orderArray as $order => $page_id) {
                                if ($page_id == $key) {
                                    $this->pageOrder[$parent_id][$order] = $newKey;
                                }
                            }

                            if ($parent_id == $key) {
                                $this->pageOrder[$newKey] = $this->pageOrder[$parent_id];
                                unset($this->pageOrder[$parent_id]);
                            }
                        }

                    } else {
                        $this->updateKey($subject, $this->inactivePages);
                    }
                    break;
            }
        }

    }

    private function updateKey(Page $subject, &$array)
    {
        $oldKey = '';
        $arrayCopy = $array;
        foreach ($arrayCopy as $key => $p) {
            if ($p === $subject) {
                $newKey = $subject->getID();
                $array[$newKey] = $array[$key];
                $oldKey = $key;
                unset($array[$key]);
            }
        }


        return $oldKey;

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


    private function insertPageID($pageID, $place, $parentPageID)
    {

        $newArray = array();
        if (!isset($this->pageOrder[$parentPageID]) || !is_array($this->pageOrder[$parentPageID])) {
            $this->pageOrder[$parentPageID] = array();
        } else {
            ksort($this->pageOrder[$parentPageID]);
        }
        $lastOrder = -1;
        $pageIsAdded = false;
        $orderNo = 0;
        $ID = '';
        $this->connection->beginTransaction();
        $statement = $this->connection->prepare("INSERT INTO PageOrder (page_id,order_no,parent_id)
        VALUES (?,?,?)");
        $this->connection->exec("DELETE FROM PageOrder WHERE page_id = '$pageID'");
        $statement->bindParam(1, $ID);
        $statement->bindParam(2, $orderNo);
        if ($parentPageID === null) {
            $this->connection->exec("DELETE FROM PageOrder WHERE parent_id IS NULL");
            $statement->bindValue(3, null, PDO::PARAM_INT);
        } else {
            $this->connection->exec("DELETE FROM PageOrder WHERE parent_id = '$parentPageID' ");
            $statement->bindValue(3, $parentPageID);
        }

        foreach ($this->pageOrder[$parentPageID] as $order => $page_id) {
            if ($place <= $order && $place > $lastOrder) {
                $ID = $pageID;
                $newArray[$orderNo] = $ID;
                $pageIsAdded = true;
                $statement->execute();
                $orderNo++;
            }
            $ID = $page_id;
            $newArray[$orderNo] = $ID;
            $lastOrder = $order;
            $statement->execute();
            $orderNo++;
        }
        if (!$pageIsAdded) {
            $ID = $pageID;
            $newArray[$orderNo] = $ID;
            $statement->execute();
        }

        $this->connection->commit();
        $this->pageOrder[$parentPageID] = $newArray;
    }

    private function removeIDFromSubLists($pageID)
    {
        foreach ($this->pageOrder as $parent_id => $orderArray) {
            foreach ($orderArray as $order => $page_id) {
                if ($page_id == $pageID) {
                    unset($this->pageOrder[$parent_id][$order]);
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
            } else if (($ret = $this->recursiveCalculatePath($page, $p) )!== false) {
                return array_merge(array($p),$ret);
            }
        }
        return false;
    }
}
