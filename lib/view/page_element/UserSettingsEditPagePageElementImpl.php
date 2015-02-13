<?php
namespace ChristianBudde\Part\view\page_element;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\view\html\FormElement;
use ChristianBudde\Part\view\html\FormElementImpl;
use ChristianBudde\Part\view\html\SelectElement;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 19/01/13
 * Time: 16:11
 */
class UserSettingsEditPagePageElementImpl extends PageElementImpl
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
    public function generateContent()
    {
        parent::generateContent();
        $output = "
        <h3>Rediger side egenskaber</h3>
        ";

        $pageForm = new FormElementImpl(FormElement::FORM_METHOD_POST);
        $pageForm->setAttributes("id", "EditPageForm");

        $pageForm->setAttributes("class", "justDistribution");
        $pageForm->insertInputText("title", "EditPageEditTitleField", $this->currentPage->getTitle(), "Titel");
        $pageForm->insertInputText("id", "EditPageEditIDField", $this->currentPage->getID(), "Side ID");
        /** @var $select SelectElement */
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
        $p = $this->currentUser->getUserPrivileges();
        if (!$p->hasSitePrivileges() && !$p->hasRootPrivileges()) {
            return $output;
        }

        $output .= "
        <h3>Administrer Brugerrettigheder</h3>
        ";
        $userList = "";
        $nonDeletableUsers = $deletableUsers = $possibleUsers = array();
        $this->generateUserList($nonDeletableUsers, $deletableUsers, $possibleUsers);

        foreach ($nonDeletableUsers as $user) {
            /** @var $user \ChristianBudde\Part\model\user\User */
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

        $addUserAccessForm = new FormElementImpl(FormElement::FORM_METHOD_POST);
        $addUserAccessForm->insertInputHidden("1", "addUserAccess");
        $addUserAccessForm->setAttributes("class", "oneLineForm");
        $addUserAccessForm->setAttributes("id", "AddUserToPageForm");
        $addUserAccessForm->insertSelect("username", "EditPageAddUserSelect", "Vælg bruger", $select);
        $select->insertOption('-- Bruger --', " ");
        foreach ($possibleUsers as $user) {
            /** @var $user User */
            $select->insertOption($user->getUsername(), $user->getUsername());
        }
        $addUserAccessForm->insertInputSubmit("Tilføj Bruger");
        $output .= $addUserAccessForm->getHTMLString();


        return $output;
    }

    private function generateUserList(array &$nonDeletableUsers = array(), array &$deletableUsers = array(), array &$possibleUsers = array())
    {
        $users = $this->userLibrary->getChildren($this->currentUser);
        foreach ($users as $user) {
            /** @var $user \ChristianBudde\Part\model\user\User */
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


}
