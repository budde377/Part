<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:59 AM
 * To change this template use File | Settings | File Templates.
 */
interface PageOrder
{

    const LIST_ACTIVE = 1;
    const LIST_INACTIVE = 2;
    const LIST_ALL = 3;

    /**
     * @abstract
     * This will return pageOrder. If null is given, it will return top-level
     * order, else if valid page id is given, it will return the order of the
     * sub-list. The return array will, if non-empty, contain instances of Page
     * If invalid id is provided, it will return empty array
     * @param null|Page $parentPage
     * @return array
     */
    public function getPageOrder(Page $parentPage = null);


    /**
     * @abstract
     * This will set the pageOrder of given page ID.
     * There must not be created loops and parent/id must be valid page ID (and existing),
     * else the function will fail and return FALSE. If proper id('s) and no loops created,
     * function will return TRUE
     * @param Page $page
     * @param int $place
     * @param null | Page $parentPage
     * @return bool
     */
    public function setPageOrder(Page $page, $place,Page $parentPage = null);


    /**
     * @abstract
     * Will return TRUE if the page is active (ie. in order), else FALSE
     * A page is only active if it is attached to root node (null)
     * @param Page $page
     * @return bool
     */
    public function isActive(Page $page);


    /**
     * @abstract
     * Will list all pages in an array as instances of Page
     * @param int $listMode Must be of ListPageEnum
     * @return array
     */
    public function listPages($listMode = PageOrder::LIST_ALL);

    /**
     * @abstract
     * @param string $id must satisfy syntax of Page id
     * @return bool | Page Returns FALSE on invalid id or other error, else instance of Page
     */
    public function createPage($id);


    /**
     * @abstract
     * This will delete a page from page order and in general
     * @param Page $page
     * @return bool
     */
    public function deletePage(Page $page);

    /**
     * @abstract
     * Will deactivate a page and all it's sub pages.
     * The page order remains the same
     * @param Page $page
     * @return void
     */
    public function deactivatePage(Page $page);

    /**
     * @abstract
     * @param string $id
     * @return Page | null Page if Page with title is found, else null
     */
    public function getPage($id);


    /**
     * @abstract
     * Will return the path of an page as an array.
     * If the page is at top level an array containing an single entrance will be returned
     * Else a numeric array with the top level as first entrance, and lowest level as last entrance
     * will be returned.
     * If a page is inactive, an empty array will be returned.
     * If a page is not found, FALSE will be returned.
     * @param Page $page
     * @return bool | array
     */
    public function getPagePath(Page $page);
}
