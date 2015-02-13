<?php
namespace ChristianBudde\Part\view\page_element;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\view\html\FormElement;
use ChristianBudde\Part\view\html\FormElementImpl;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 20/01/13
 * Time: 17:24
 */
class UserSettingsEditUserPageElementImpl extends PageElementImpl
{
    private $container;
    private $currentUser;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->currentUser = $container->getUserLibraryInstance()->getUserLoggedIn();
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
        <h3>Rediger brugernavn og e-mail</h3>";
        $userNameMailForm = new FormElementImpl(FormElement::FORM_METHOD_POST);
        if ($this->evaluateUsernameForm($status, $message)) {
            $userNameMailForm->setNotion($message, $status);
        }
        $userNameMailForm->setAttributes("class", "verticalAlignForm");
        $userNameMailForm->setAttributes("id", "UpdateUsernameMailForm");
        $userNameMailForm->insertInputText("username", "EditUserEditUsernameField", $this->currentUser->getUsername(), "Brugernavn");
        $userNameMailForm->insertInputText("mail", "EditUserEditMailField", $this->currentUser->getMail(), "E-Mail");
        $userNameMailForm->insertInputSubmit("Rediger");
        $output .= $userNameMailForm->getHTMLString();
        $output .= "
        <h3>Rediger kodeord</h3>";

        $passwordForm = new FormElementImpl(FormElement::FORM_METHOD_POST);
        if ($this->evaluatePasswordForm($status, $message)) {
            $passwordForm->setNotion($message, $status);
        }
        $passwordForm->setAttributes("class", "verticalAlignForm");
        $passwordForm->setAttributes("id", "UpdatePasswordForm");
        $passwordForm->insertInputPassword("old_password", "EditUserEditPasswordOldField", "", "Gammelt kodeord");
        $passwordForm->insertInputPassword("new_password", "EditUserEditPasswordNewField", "", "Nyt kodeord");
        $passwordForm->insertInputPassword("new_password_repeat", "EditUserEditPasswordNewRepField", "", "Gentag kodeord");
        $passwordForm->insertInputSubmit("Rediger");
        $output .= $passwordForm->getHTMLString();

        return $output;
    }


    private function evaluateUsernameForm(&$status = null, &$message = null)
    {
        if (isset($_POST['username'], $_POST['mail'])) {
            $username = trim($_POST['username']);
            $mail = trim($_POST['mail']);
            if (!$this->currentUser->isValidMail($mail)) {
                $status = FormElement::NOTION_TYPE_ERROR;
                $message = "Ugyldig E-mail";
                return true;
            }

            if ($username != $this->currentUser->getUsername() && !$this->currentUser->isValidUsername($username)) {
                $status = FormElement::NOTION_TYPE_ERROR;
                $message = "Ugyldig Brugernavn";
            } else {
                $this->currentUser->setUsername($username);
                $this->currentUser->setMail($mail);
                $status = FormElement::NOTION_TYPE_SUCCESS;
                $message = "Ændringerne er gemt";
            }
            return true;

        }
        return false;
    }

    private function evaluatePasswordForm(&$status = null, &$message = null)
    {
        if (isset($_POST['old_password'], $_POST['new_password'], $_POST['new_password_repeat'])) {
            $oldPassword = $_POST['old_password'];
            $newPassword = $_POST['new_password'];
            $newPasswordRepeat = $_POST['new_password_repeat'];
            if (!$this->currentUser->verifyLogin($oldPassword)) {
                $status = FormElement::NOTION_TYPE_ERROR;
                $message = "Forkert gammelt kodeord";
                return true;
            }
            if (!$this->currentUser->isValidPassword($newPassword)) {
                $status = FormElement::NOTION_TYPE_ERROR;
                $message = "Dit nye kodeord er ugyldigt";
                return true;
            }

            if ($newPassword == $newPasswordRepeat) {
                $this->currentUser->setPassword($newPassword);
                $status = FormElement::NOTION_TYPE_SUCCESS;
                $message = "Dit kodeord er ændret";
            } else {
                $status = FormElement::NOTION_TYPE_ERROR;
                $message = "Nye kodeord var ikke ens";
            }

            return true;

        }

        return false;
    }

}
