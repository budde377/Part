<?php
namespace ChristianBudde\Part\model\page;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\util\traits\RequestTrait;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/20/12
 * Time: 12:50 PM
 */
class CurrentPageStrategyImpl implements CurrentPageStrategy, \Serializable
{
    use RequestTrait;
    private $pageOrder;
    private $defaultPagesLib;
    private $currentPagePath = null;
    private $container;

    public function __construct(BackendSingletonContainer $container)
    {

        $this->container = $container;
        $this->defaultPagesLib = $container->getDefaultPageLibraryInstance();
        $this->pageOrder = $container->getPageOrderInstance();

    }

    /**
     * Will return the path to the current page as an array of
     * Page's
     *
     * @return array
     */
    public function getCurrentPagePath()
    {

        if ($this->currentPagePath != null) {
            return $this->currentPagePath;
        }


        $pageOrderArray = $this->pageOrder->getPageOrder();
        $path = $this->GETValueOfIndexIfSetElseDefault('page', false);


        if (!empty($path) && !empty($pagePath = $this->getPathArrayFromString($path, $pageOrderArray))) {
            return $this->currentPagePath = $pagePath;
        }

        if (!empty($pageOrderArray) && empty($path)) {
            return $this->currentPagePath = [array_shift($pageOrderArray)];
        }

        return $this->currentPagePath = [new NotFoundPageImpl($this->container)];
    }

    /**
     * @return Page
     */
    public function getCurrentPage()
    {
        $pageArray = $this->getCurrentPagePath();
        return array_pop($pageArray);


    }

    private function getPathArrayFromString($path, $pageOrderArray)
    {
        $pathArray = explode('/', $path);
        $pathArray = array_filter($pathArray, function ($v) {
            return !empty($v);
        });
        $activePagePath = $this->generatePathFromActivePages($pathArray, $pageOrderArray);

        if (count($activePagePath) == count($pathArray)) {
            return $activePagePath;
        }

        if(count($pathArray) != 1){
            return [];
        }
        $page = $this->firstPageMatch($pathArray[0], $this->pageOrder->listPages(PageOrder::LIST_INACTIVE));
        if($page != null){
            return [$page];
        }

        if($this->defaultPagesLib == null){
            return [];
        }
        $page = $this->firstPageMatch($pathArray[0], $this->defaultPagesLib->listPages());

        return $page == null?[]:[$page];

    }

    /**
     * @param string[] $path
     * @param Page[] $pageOrder
     * @return array
     */
    private function generatePathFromActivePages($path, $pageOrder)
    {
        if (empty($path)) {
            return [];
        }
        $first_segment = array_shift($path);

        if(($page = $this->firstPageMatch($first_segment, $pageOrder)) == null){
            return [];
        }

        return array_merge([$page], $this->generatePathFromActivePages($path, $this->pageOrder->getPageOrder($page)));


    }



    /**
     * @param string $segment
     * @param Page[] $pages
     * @return Page
     */
    private function firstPageMatch($segment, $pages)
    {
        foreach ($pages as $page) {
            if ($page->match($segment)) {
                return $page;
            }
        }
        return null;
    }


    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize([$this->container, $this->pageOrder, $this->defaultPagesLib]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $array = unserialize($serialized);
        $this->container = $array[0];
        $this->pageOrder = $array[1];
        $this->defaultPagesLib = $array[2];
    }
}
