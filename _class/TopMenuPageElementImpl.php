<?php
require_once dirname(__FILE__) . '/../_interface/PageElement.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 19:54
 */
class TopMenuPageElementImpl implements PageElement
{
    private $pageOrder;
    /** @var Page */
    private $currentPage;
    function __construct(BackendSingletonContainer $container)
    {
        $this->pageOrder = $container->getPageOrderInstance();
        $this->currentPage = $container->getCurrentPageStrategyInstance()->getCurrentPage();
    }

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function getContent()
    {
        $out = "";
        $pageOrder = $this->pageOrder->getPageOrder();

        /** @var $page Page */
        foreach($pageOrder as $page){
            $id = $page->getID();
            $active = $this->currentPage === $page?"class='active'":"";
            $hidden = $page->isHidden()?'hidden':'';
            $out .= "
            <li $active $hidden>
                <a href='/$id'>{$page->getTitle()}</a>
            </li>
            ";
        }
        return "<ul>$out</ul>";
    }
}
