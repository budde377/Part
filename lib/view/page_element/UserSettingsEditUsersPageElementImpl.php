<?php
namespace ChristianBudde\cbweb\view\page_element;
use ChristianBudde\cbweb\BackendSingletonContainer;
use ChristianBudde\cbweb\model\user\User;
use ChristianBudde\cbweb\view\html\HTMLFormElement;
use ChristianBudde\cbweb\view\html\HTMLFormElementImpl;
use ChristianBudde\cbweb\view\html\HTMLSelectElement;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 20/01/13
 * Time: 20:12
 */
class UserSettingsEditUsersPageElementImpl extends PageElementImpl
{
    private $container;
    private $userLibrary;
    /** @var null|User */
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


    private function userToLi(User $user)
    {
        $privileges = $user->getUserPrivileges();
        $pages = "";
        $first = true;
        foreach ($privileges->listPagePrivileges() as $pageString) {
            if(!$first){
                $pages .= " ";
            }
            $pages .= $pageString ;
            $first = false;
        }
        $current = $user->isLoggedIn() ? "current" : "";
        return "
            <li class='$current' data-parent='{$user->getParent()}' data-mail='{$user->getMail()}' data-username='{$user->getUsername()}' data-privileges='{$this->userPrivilegeString($user, true)}' data-pages='$pages' data-last-login='{$user->getLastLogin()}'>
                <a href='mailto:{$user->getMail()}' class='val'>{$user->getUsername()}</a>, <span class='privileges'>({$this->userPrivilegeString($user)})</span>
                <div class='delete link' title='Slet'>&nbsp;</div>
                <div class='time link'>&nbsp;</div>
            </li>";
    }

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        parent::generateContent();
        $this->evaluateDeleteUser($status);
        $output = "
        <h3>Brugere</h3>";

        $list = $this->userToLi($this->currentUser);
        $addUserForm = new HTMLFormElementImpl(HTMLFormElement::FORM_METHOD_POST);
        if ($this->evaluateAddUserForm($status, $message)) {
            $addUserForm->setNotion($message, $status);
        }
        foreach ($this->userLibrary->getChildren($this->currentUser) as $user) {
            /** @var $user User */
            $list .= $this->userToLi($user);
        }

        $output .= "
        <ul class='colorList' id='UserList'>
            $list
        </ul>
        ";

        if ($this->currentUserPrivileges->hasRootPrivileges() || $this->currentUserPrivileges->hasSitePrivileges()) {
            $output .= "
            <h3>Tilføj bruger</h3>";
            $addUserForm->setAttributes("id", "EditUsersAddUserForm");
            $addUserForm->setAttributes("class", "justDistribution");
            $addUserForm->insertInputText("mail", "AddUserMailField", "", "E-Mail");
            $addUserForm->insertSelect("level", "AddUserLevelSelect", "Rettigheder", $select);
            /** @var $select HTMLSelectElement */
            $select->insertOption("Side", "page");
            $select->insertOption("Website", "site");
            if ($this->currentUserPrivileges->hasRootPrivileges()) {
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

    private function userPrivilegeString(User $user, $simple = false)
    {
        $privileges = $user->getUserPrivileges();
        return ($privileges->hasRootPrivileges() ? ($simple ? "root" : "Root") : ($privileges->hasSitePrivileges() ? ($simple ? "site" : "Website") : ($simple ? "page" : "Side"))) . ($simple ? "" : " Administrator");
    }

}
