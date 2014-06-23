<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 20/01/13
 * Time: 02:54
 */
class UserSettingsEditPagesPageElementImpl extends PageElementImpl
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
        $this->evaluateForm();
        $this->evaluateDeletePage();
        $this->evaluateActivatePage();
        $this->evaluateDeactivatePage();
        $levelClass = !$this->currentUserPrivileges->hasRootPrivileges() && !$this->currentUserPrivileges->hasSitePrivileges()? 'levelPage':'draggable';
        $output = "<h3>Aktive sider</h3>";

        $output .= "
        <div id='ActiveListPath'>
            <span class='up hidden'> Tilbage </span>
            <span class='dot'> </span>  /
        </div>
        {$this->recursivePageListGenerator(null,"id='ActivePageList'",$levelClass)}
        ";



        $list = "";
        foreach($this->pageOrder->listPages(PageOrder::LIST_INACTIVE) as $page){
            /** @var $page Page */
            $current = $page->getID() == $this->currentPage->getID()?'current':'';
            $list .= "
            <li class='$current' {$this->pageDataSetGenerator($page)}>
                <div class='padding'> &nbsp;</div>
                <a href='/{$page->getID()}' class='val'>{$page->getTitle()}</a>
                <div class='link delete' title='Slet'> &nbsp; </div>
                <div class='link activate' title='Aktiver'> &nbsp; </div>
            </li>
            ";
        }
        if($list == ""){
            $list = "<li class='emptyListInfo'> Der er ingen inaktive sider</li>";
        }

        $output .= "
        <h3>Inaktive sider</h3>
        <ul class='colorList $levelClass' id='InactivePageList' >
            $list
        </ul>";

        if($levelClass == 'draggable'){

            $form = new HTMLFormElementImpl(HTMLFormElement::FORM_METHOD_POST);
            $form->setAttributes("class","oneLineForm");
            $form->setAttributes("id","EditPagesForm");
            $form->insertInputText("title","EditPagesAddPage","","Side titel");
            $form->insertInputHidden("1","addPageForm");
            $form->insertInputSubmit("Opret");

            $output .= $form->getHTMLString();

        }

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

    private function evaluateForm(&$status = null,&$newId = null)
    {
        if(isset($_POST['addPageForm'],$_POST['title'])){
            $title = trim($_POST['title']);
            if(strlen($title) > 0 ){
                $id = strtolower($title);
                $id = $baseId = preg_replace('/[^a-z0-9\-_]/','_',$id);
                $i = 2;
                while(($p = $this->pageOrder->createPage($id)) === false){
                    $id = $baseId."_".$i;
                    $i++;
                }
                $newId = $id;
                $p->setTitle($title);
                $p->setTemplate('main');
                $status = HTMLFormElement::NOTION_TYPE_SUCCESS;
                return true;
            }
            $status = HTMLFormElement::NOTION_TYPE_ERROR;
            return true;
        }
        return false;
    }

    private function evaluateDeletePage(&$status = null){
        if(isset($_POST['deletePageFromPages'],$_POST['id'])){
            $id = trim($_POST['id']);
            if(($p = $this->pageOrder->getPage($id)) != null && $this->pageOrder->deletePage($p)){
                $status = HTMLFormElement::NOTION_TYPE_SUCCESS;
            } else {
                $status = HTMLFormElement::NOTION_TYPE_ERROR;
            }
            return true;
        }
        return false;
    }

    private function evaluateActivatePage(&$status = null)
    {
        if(isset($_POST['activatePage'],$_POST['id'])){
            $id = trim($_POST['id']);
            if(($p = $this->pageOrder->getPage($id)) != null && !$this->pageOrder->isActive($p)){
                $this->pageOrder->setPageOrder($p);
                $status = HTMLFormElement::NOTION_TYPE_SUCCESS;
            } else {
                $status = HTMLFormElement::NOTION_TYPE_ERROR;
            }
            return true;
        }
        return false;
    }

    private function evaluateDeactivatePage(&$status = null)
    {
        if(isset($_POST['deactivatePage'],$_POST['id'])){
            $id = trim($_POST['id']);
            if(($p = $this->pageOrder->getPage($id)) != null && $this->pageOrder->isActive($p)){
                $this->pageOrder->deactivatePage($p);
                $status = HTMLFormElement::NOTION_TYPE_SUCCESS;
            } else {
                $status = HTMLFormElement::NOTION_TYPE_ERROR;
            }
            return true;
        }
        return false;
    }

    private function evaluateUpdatePageOrderPage(&$status = null)
    {
        if(isset($_POST['updatePageOrder'],$_POST['pageOrder']) && is_array($_POST['pageOrder'])){
            $postArray = $_POST['pageOrder'];
            ksort($postArray);
            foreach($postArray as $key=>$id){
                $id = trim($id);
                $page = $this->pageOrder->getPage($id);
                $this->pageOrder->setPageOrder($page,$key);
            }
            $status = HTMLFormElement::NOTION_TYPE_SUCCESS;
            return true;
        }
        return false;
    }





}
