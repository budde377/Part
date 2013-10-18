<?php
require_once dirname(__FILE__) . '/../_interface/PageElement.php';
require_once dirname(__FILE__) . '/HTMLFormElementImpl.php';
require_once dirname(__FILE__) . '/../_interface/Registrable.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 19/01/13
 * Time: 16:11
 */
class UserSettingsEditPagePageElementImpl implements PageElement
{
    private $container;
    private $currentPage;
    private $userLibrary;
    private $currentUser;
    private $config;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->currentPage = $container->getCurrentPageStrategyInstance()->getCurrentPage();
        $this->userLibrary = $container->getUserLibraryInstance();
        $this->currentUser = $this->userLibrary->getUserLoggedIn();
        $this->config = $container->getConfigInstance();
    }


    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function getContent()
    {
        $output = "
        <h3>Rediger side egenskaber</h3>
        ";

        $pageForm = new HTMLFormElementImpl(HTMLFormElement::FORM_METHOD_POST);
        $pageForm->setAttributes("id", "EditPageForm");
        if ($this->evaluatePageForm($status, $message)) {
            $pageForm->setNotion($message, $status);
        }
        $pageForm->setAttributes("class", "justDistribution");
        $pageForm->insertInputText("title", "EditPageEditTitleField", $this->currentPage->getTitle(), "Titel");
        $pageForm->insertInputText("id", "EditPageEditIDField", $this->currentPage->getID(), "Side ID");
        /** @var $select HTMLSelectElement */
        $pageForm->insertSelect("template", "EditPageEditTemplateSelect", "Side type", $select);
        foreach ($this->config->listTemplateNames() as $templateName) {
            if (substr($templateName, 0, 1) != "_") {
                $option = $select->insertOption($templateName, $templateName);
                if ($templateName == $this->currentPage->getTemplate()) {
                    $option->setSelected(true);
                }

            }
        }


        $pageForm->insertInputText("alias", "EditPageEditAliasField", $this->currentPage->getAlias(), "Alias (regexp)");
        $pageForm->insertInputSubmit("Rediger");

        $output .= $pageForm->getHTMLString();

        $output .= "
        <h3>Administrer Brugerrettigheder</h3>
        ";
        $userList = "";
        $this->generateUserList($nonDeletableUsers, $deletableUsers, $possibleUsers);
        if ($this->evaluateRemoveUser($deletableUsers)) {
            $nonDeletableUsers = $deletableUsers = $possibleUsers = array();
            $this->generateUserList($nonDeletableUsers, $deletableUsers, $possibleUsers);
        }
        foreach ($nonDeletableUsers as $user) {
            /** @var $user User */
            $userList .= "<li>{$user->getUsername()}</li>";
        }
        foreach ($deletableUsers as $user) {
            $userList .= "<li><span class='val'>{$user->getUsername()}</span><div class='delete link' title='Slet'>&nbsp;</div></li>";
        }
        $output .= "
        <ul class='colorList' id='PageUserList'>
            <li>{$this->currentUser->getUsername()}</li>
            $userList
        </ul>
        ";

        $addUserAccessForm = new HTMLFormElementImpl(HTMLFormElement::FORM_METHOD_POST);
        $this->evaluateAddUserAccess($possibleUsers);
        $addUserAccessForm->insertInputHidden("1", "addUserAccess");
        $addUserAccessForm->setAttributes("class", "oneLineForm");
        $addUserAccessForm->setAttributes("id", "AddUserToPageForm");
        $addUserAccessForm->insertSelect("username", "EditPageAddUserSelect", "Vælg bruger", $select);
        $select->insertOption('-- Bruger --'," ");
        foreach ($possibleUsers as $user) {
            /** @var $user User */
            $select->insertOption($user->getUsername(), $user->getUsername());
        }
        $addUserAccessForm->insertInputSubmit("Tilføj Bruger");
        $output .= $addUserAccessForm->getHTMLString();
/*
        $output .= "
        <h3>Administrer undersider</h3>
        <ul class='colorList draggable' id='PageUserList'>
            <li>Testside 1</li>
            <li>Testside 2</li>
        </ul>
        ";
        $createSubPageForm = new HTMLFormElementImpl(HTMLFormElement::FORM_METHOD_POST);
        $createSubPageForm->setAttributes('id','AddSubPageForm');
        $createSubPageForm->insertRadioButton('chooseMethod','ChooseMethod1','create','Opret og tilføj ny side');
        $createSubPageForm->insertInputText('title','AddSubPageTitle','','Side titel')
            ->setAttributes('class','hidden');
        $createSubPageForm->insertRadioButton('chooseMethod','ChooseMethod2','activate','Tilføj inaktiv side');
        $createSubPageForm->insertSelect('activateTitle','SelectInactiveSubPage','Inaktiv side',$select)
            ->setAttributes('class','hidden');

        $createSubPageForm->insertInputSubmit('Tilføj  Underside');
        $output .= $createSubPageForm->getHTMLString();
*/

        return $output;
    }

    private function generateUserList(&$nonDeletableUsers = array(), &$deletableUsers = array(), &$possibleUsers = array())
    {
        $possibleUsers = is_array($possibleUsers) ? $possibleUsers : array();
        $nonDeletableUsers = is_array($nonDeletableUsers) ? $nonDeletableUsers : array();
        $deletableUsers = is_array($nonDeletableUsers) ? $nonDeletableUsers : array();
        $users = $this->userLibrary->getChildren($this->currentUser);
        foreach ($users as $user) {
            /** @var $user User */
            $privilege = $user->getUserPrivileges();
            if ($privilege->hasPagePrivileges($this->currentPage)) {
                if (!$privilege->hasRootPrivileges() && !$privilege->hasSitePrivileges()) {
                    $deletableUsers[] = $user;
                } else {
                    $nonDeletableUsers[] = $user;
                }
            } else {
                $possibleUsers[] = $user;
            }
        }
    }

    private function evaluatePageForm(&$status, &$message)
    {
        if (isset($_POST['title'], $_POST['id'], $_POST['template'], $_POST['alias'])) {
            $alias = trim($_POST['alias']);
            $id = trim($_POST['id']);
            $template = trim($_POST['template']);
            $title = trim($_POST['title']);

            if ($this->currentPage->getID() != $id && !$this->currentPage->isValidId($id)) {
                $status = HTMLFormElement::NOTION_TYPE_ERROR;
                $message = "Ugyldigt ID";
                return true;
            }

            if (!$this->currentPage->isValidAlias($alias)) {
                $status = HTMLFormElement::NOTION_TYPE_ERROR;
                $message = "Ugyldigt alias";
                return true;
            }
            if (array_search($template, $this->config->listTemplateNames()) === false) {
                $status = HTMLFormElement::NOTION_TYPE_ERROR;
                $message = "Ugyldig side type";
            }
            $this->currentPage->setID($id);
            $this->currentPage->setTitle($title);
            $this->currentPage->setAlias($alias);
            $this->currentPage->setTemplate($template);

            $status = HTMLFormElement::NOTION_TYPE_SUCCESS;
            $message = "Dine ændringer er gemt";
            return true;
        }
        return false;
    }

    private function evaluateAddUserAccess($possibleUsers, &$status = null)
    {
        if (isset($_POST['username'], $_POST['addUserAccess'])) {
            $username = $_POST['username'];
            if (array_search($username, array_map(function (User $u) {
                return $u->getUsername();
            }, $possibleUsers)) !== false
            ) {
                $user = $this->userLibrary->getUser($username);
                $privileges = $user->getUserPrivileges();
                $privileges->addPagePrivileges($this->currentPage);
                $status = HTMLFormElement::NOTION_TYPE_SUCCESS;
            } else {
                $status = HTMLFormElement::NOTION_TYPE_ERROR;
            }
            return true;
        }
        return false;
    }


    private function evaluateRemoveUser($deletableUsers, &$status = null)
    {
        if (isset($_POST['username'], $_POST['removeUserAccess'])) {
            $username = trim($_POST['username']);
            $delUsers = array_map(function (User $u) {
                return $u->getUsername();
            }, $deletableUsers);
            if (array_search($username, $delUsers) !== false) {
                $user = $this->userLibrary->getUser($username);
                $privileges = $user->getUserPrivileges();
                $privileges->revokePagePrivileges($this->currentPage);
                $status = HTMLFormElement::NOTION_TYPE_SUCCESS;
            } else {
                $status = HTMLFormElement::NOTION_TYPE_ERROR;
            }
            return true;
        }
        return false;
    }


}
