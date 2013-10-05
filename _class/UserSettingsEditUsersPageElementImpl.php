<?php
require_once dirname(__FILE__) . "/../_interface/PageElement.php";
require_once dirname(__FILE__) . "/../_interface/Registrable.php";
require_once dirname(__FILE__) . "/UserImpl.php";
require_once dirname(__FILE__) . "/HTMLFormElementImpl.php";
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 20/01/13
 * Time: 20:12
 */
class UserSettingsEditUsersPageElementImpl implements PageElement
{
    private $container;
    private $userLibrary;
    /** @var null|\User  */
    private $currentUser;
    private $currentUserPrivileges;
    private $pageOrder;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->userLibrary = $container->getUserLibraryInstance();
        $this->currentUser = $this->userLibrary->getUserLoggedIn();
        $this->currentUserPrivileges = $this->currentUser->getUserPrivileges();
        $this->pageOrder = $container->getPageOrderInstance();
    }


    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function getContent()
    {
        $this->evaluateDeleteUser($status);
        $output = "
        <h3>Brugere</h3>";

        $mail = $this->currentUser->getMail();
        $list = "
        <li class='current'>
            <a href='mailto:$mail' class='val'>{$this->currentUser->getUsername()}</a>, <span class='privileges'>({$this->userPrivilegeString($this->currentUser)})</span>
            <span class='parent'>{$this->currentUser->getParent()}</span>
        </li>
        ";
        $addUserForm = new HTMLFormElementImpl(HTMLFormElement::FORM_METHOD_POST);
        if ($this->evaluateAddUserForm($status, $message)) {
            $addUserForm->setNotion($message, $status);
        }
        foreach ($this->userLibrary->getChildren($this->currentUser) as $user) {
            /** @var $user User */
            $privileges = $user->getUserPrivileges();
            $pages = "";
            if(!$privileges->hasRootPrivileges() && !$privileges->hasSitePrivileges()){
                /** @var $page Page */
                foreach($this->pageOrder->listPages() as $page){
                    $pages .= $privileges->hasPagePrivileges($page)?$page->getID()." ":"";
                }
                $pages = "<span class='pages hidden'>{$pages}</span>";

            }

            $list .= "
            <li>
                <a href='mailto:{$user->getMail()}' class='val'>{$user->getUsername()}</a>, <span class='privileges'>({$this->userPrivilegeString($user)})</span>
                <span class='parent hidden'>{$user->getParent()}</span>
                <div class='delete link' title='Slet'>&nbsp;</div>
                $pages
            </li>";
        }

        $output .= "
        <ul class='colorList' id='UserList'>
            $list
        </ul>
        ";

        if($this->currentUserPrivileges->hasRootPrivileges() || $this->currentUserPrivileges->hasSitePrivileges()){
            $output .= "
            <h3>Tilføj bruger</h3>";
            $addUserForm->setAttributes("id", "EditUsersAddUserForm");
            $addUserForm->setAttributes("class", "justDistribution");
            $addUserForm->insertInputText("mail", "AddUserMailField", "", "E-Mail");
            $addUserForm->insertSelect("level", "AddUserLevelSelect", "Rettigheder", $select);
            /** @var $select HTMLSelectElement */
            $select->insertOption("Side", "page");
            $select->insertOption("Website", "site");
            if($this->currentUserPrivileges->hasRootPrivileges()){
                $select->insertOption("Root", "root");
            }
            $addUserForm->insertInputSubmit("Opret bruger");
            $output .= $addUserForm->getHTMLString();

        }

        return $output;
    }

    private function evaluateAddUserForm(&$status = null, &$message = null, &$username = null)
    {
        if (isset($_POST['level'], $_POST['mail'])) {
            $mail = trim($_POST['mail']);
            if (!$this->currentUser->isValidMail($mail)) {
                $status = HTMLFormElement::NOTION_TYPE_ERROR;
                $message = "Ugyldig E-mail";
                return true;
            }
            $uName = explode("@", $mail);
            $uName = $baseUsername = $uName[0];
            $index = 2;
            while (!$this->currentUser->isValidUsername($uName)) {
                $uName = $baseUsername . "_" . $index;
                $index++;
            }
            $password = uniqid();
            $level = trim($_POST['level']);
            if ($level != "page" && $level != "site" && $level != "root") {
                $status = HTMLFormElement::NOTION_TYPE_ERROR;
                $message = "Ugyldig privilegie";
                return true;
            }
            $l1 = $this->privilegesToInt($this->currentUserPrivileges->hasRootPrivileges(), $this->currentUserPrivileges->hasSitePrivileges());
            $l2 = $this->privilegesToInt($level == "root", $level == "site");
            if ($l1 == 1 || $l1 < $l2) {
                $status = HTMLFormElement::NOTION_TYPE_ERROR;
                $message = "Kunne ikke oprette bruger";
                return true;
            }

            $user = $this->userLibrary->createUser($uName, $password, $mail, $this->currentUser);
            $privileges = $user->getUserPrivileges();
            switch ($level) {
                case "root":
                    $privileges->addRootPrivileges();
                    break;
                case "site":
                    $privileges->addSitePrivileges();
                    break;
            }
            $username = $uName;
            $status = HTMLFormElement::NOTION_TYPE_SUCCESS;
            $message = "Brugeren er oprettet";
            return true;
        }
        return false;
    }

    private function evaluateDeleteUser(&$status = null)
    {

        if (isset($_POST['username'], $_POST['delete_user'])) {
            $username = trim($_POST['username']);
            $isChild = false;
            foreach ($this->userLibrary->getChildren($this->currentUser) as $user) {
                /** @var $user User */
                $isChild = $isChild || $username == $user->getUsername();
            }
            if ($this->userLibrary->getUser($username) != null && $isChild) {
                $this->userLibrary->getUser($username)->delete();
                $status = HTMLFormElement::NOTION_TYPE_SUCCESS;
            } else {
                $status = HTMLFormElement::NOTION_TYPE_ERROR;
            }
            return true;
        }
        return false;

    }

    private function privilegesToInt($root, $site)
    {
        return $root ? 3 : ($site ? 2 : 1);
    }

    private function userPrivilegeString(User $user)
    {
        $privileges = $user->getUserPrivileges();
        return ($privileges->hasRootPrivileges() ? "Root" : ($privileges->hasSitePrivileges() ? "Website" : "Side")) . " Administrator";
    }





}