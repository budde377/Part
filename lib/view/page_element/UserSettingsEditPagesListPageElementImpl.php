<?php
namespace ChristianBudde\Part\view\page_element;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\page\Page;
use ChristianBudde\Part\model\page\PageOrder;
use ChristianBudde\Part\view\html\FormElement;
use ChristianBudde\Part\view\html\FormElementImpl;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 20/01/13
 * Time: 02:54
 */
class UserSettingsEditPagesListPageElementImpl extends PageElementImpl
{

    private $container;
    private $pageOrder;
    private $currentPage;
    private $currentUser;
    private $currentUserPrivileges;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->pageOrder = $container->getPageOrderInstance();
        $this->currentPage = $container->getCurrentPageStrategyInstance()->getCurrentPage();
        $this->currentUser = $container->getUserLibraryInstance()->getUserLoggedIn();
        $this->currentUserPrivileges = $this->currentUser->getUserPrivileges();
    }


    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        parent::generateContent();

        $levelClass = !$this->currentUserPrivileges->hasRootPrivileges() && !$this->currentUserPrivileges->hasSitePrivileges()? 'levelPage':'draggable';

        $output = "
        <div id='ActiveListPath'>
            <span class='up hidden'> Tilbage </span>
            <span class='dot'> </span>  /
        </div>
        {$this->recursivePageListGenerator(null,"id='ActivePageList'",$levelClass)}
        ";

        return $output;

    }

    public function pageDataSetGenerator(Page $page)
    {
        $hidden = $page->isHidden()?"true":"false";
        return "data-id='{$page->getId()}' data-template='{$page->getTemplate()}' data-alias='{$page->getAlias()}' data-title='{$page->getTitle()}' data-hidden='$hidden'";
    }

    private function recursivePageListGenerator($parentPage= null, $attr="", $class = "",$path="/"){
        $list = "";
        foreach($this->pageOrder->getPageOrder($parentPage) as $page){
            /** @var $page Page */
            $current = $page->getID() == $this->currentPage->getID()?'current':'';
            $pageId = $page->getID();
            $current .= $page->isHidden()?" ishidden":"";
            $t = $page->isHidden()?"Vis":"Skjul";
            $list .= "
            <li class='$current' {$this->pageDataSetGenerator($page)}>
                <div class='padding'> &nbsp;</div>
                <a href='$path$pageId' class='val'>{$page->getTitle()}</a>
                <div class='link delete' title='Slet'>&nbsp;</div>
                <div class='link activate' title='Deaktiver'>&nbsp;</div>
                <div class='link showhide' title='$t'> &nbsp;</div>
                <div class='link subpages' title='Undersider'>&nbsp;</div>
                {$this->recursivePageListGenerator($page,"","",$path.$pageId."/")}
            </li>
            ";
        }
        if($list == ""){
            $list = "<li class='emptyListInfo'>Der er ingen aktive sider</li>";
        }
        return "<ul $attr class='colorList $class'> $list </ul>";
    }






}
