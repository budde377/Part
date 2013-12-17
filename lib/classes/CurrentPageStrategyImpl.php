<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/20/12
 * Time: 12:50 PM
 */
class CurrentPageStrategyImpl implements CurrentPageStrategy
{
    use RequestTrait;
    private $pageOrder;
    private $defaultPages;
    private $currentPagePath = null;

    public function __construct(PageOrder $pageOrder, DefaultPageLibrary $defaultPages)
    {
        $this->defaultPages = $defaultPages;
        $this->pageOrder = $pageOrder;

    }

    /**
     * Will return the path to the current page as an array of
     * Page's
     *
     * @return array
     */
    public function getCurrentPagePath()
    {

        if ($this->currentPagePath !== null) {
            return $this->currentPagePath;
        }

        $returnArray = array();

        $pageOrderArray = $this->pageOrder->getPageOrder();
        $arrayCopy = $pageOrderArray;

        if (($path = $this->GETValueOfIndexIfSetElseDefault('page', false)) !== false) {
            $pathArray = explode('/', $path);
            $emptyFilter = function($v)
            {
                return !empty($v);
            };
            $pathArray = array_filter($pathArray, $emptyFilter);
            $firstPathElement = isset($pathArray[0]) && count($pathArray) == 1 ? $pathArray[0] : false;

            $notFound = false;
            $resultPage = null;
            while (count($pathArray) && !$notFound) {
                $path = array_shift($pathArray);
                if ($resultPage !== null) {
                    $returnArray[] = $resultPage;
                }
                $resultPage = null;
                while (count($arrayCopy) && $resultPage == null) {
                    /** @var $p Page */
                    $p = array_shift($arrayCopy);
                    if ($p->match($path)) {
                        $resultPage = $p;
                        $arrayCopy = $this->pageOrder->getPageOrder($p);
                    }
                }
                $notFound = $resultPage == null;
            }


            if (!$notFound) {
                $returnArray[] = $resultPage;
            } else {
                $returnArray = array();
                if ($firstPathElement !== false) {

                    $pageList = $this->pageOrder->listPages(PageOrder::LIST_INACTIVE);
                    $inactiveNotFound = true;
                    while ($inactiveNotFound && count($pageList)) {
                        /** @var $inactivePage Page */
                        $inactivePage = array_shift($pageList);
                        if ($inactivePage->match($firstPathElement)) {
                            $inactiveNotFound = false;
                            $returnArray[] = $inactivePage;
                        }
                    }
                    if($inactiveNotFound && $this->defaultPages !== null){
                        $defaultPages = $this->defaultPages->listPages();
                        $defaultPageNotFound = true;
                        while(count($defaultPages) && $defaultPageNotFound){
                            /** @var $page Page */
                            $page = array_shift($defaultPages);
                            if($page->match($firstPathElement)){
                                $defaultPageNotFound = false;
                                $returnArray[] = $page;
                            }
                        }

                    }
                }
            }


        } else if (count($arrayCopy)) {
            $page = array_shift($arrayCopy);
            $returnArray[] = $page;
        }

        if (!count($returnArray)) {
            $returnArray[] = new NotFoundPageImpl();
        }

        $this->currentPagePath = $returnArray;
        return $returnArray;
    }

    /**
     * @return Page
     */
    public function getCurrentPage()
    {
        $pageArray = $this->getCurrentPagePath();
        return array_pop($pageArray);


    }

}
