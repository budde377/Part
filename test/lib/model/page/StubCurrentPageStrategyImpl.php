<?php
namespace ChristianBudde\Part\model\page;

/**
 * User: budde
 * Date: 9/8/13
 * Time: 11:36 PM
 */

class StubCurrentPageStrategyImpl implements CurrentPageStrategy
{

    private $currentPagePath;
    private $currentPage;

    /**
     * @return mixed
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param mixed $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return mixed
     */
    public function getCurrentPagePath()
    {
        return $this->currentPagePath;
    }

    /**
     * @param mixed $currentPagePath
     */
    public function setCurrentPagePath($currentPagePath)
    {
        $this->currentPagePath = $currentPagePath;
    }

}