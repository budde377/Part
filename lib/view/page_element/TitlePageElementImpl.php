<?php
namespace ChristianBudde\Part\view\page_element;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\page\Page;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/15/12
 * Time: 6:22 PM
 */
class TitlePageElementImpl extends PageElementImpl
{
    private $backendSingletonContainer;
    private $pageOrder;
    private $currentPageStrategy;

    public function __construct(BackendSingletonContainer $backendSingletonContainer)
    {
        $this->backendSingletonContainer = $backendSingletonContainer;
        $this->pageOrder = $backendSingletonContainer->getPageOrderInstance();
        $this->currentPageStrategy = $backendSingletonContainer->getCurrentPageStrategyInstance();

    }

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        parent::generateContent();
        $pathArray = $this->currentPageStrategy->getCurrentPagePath();
        $titleString = '';
        foreach ($pathArray as $page) {
            /** @var $page Page */

            $titleString .= ' - ' . (($t = $page->getTitle()) == '_404' ? 'Siden blev ikke fundet' : $t);
        }

        return $titleString;
    }

}
