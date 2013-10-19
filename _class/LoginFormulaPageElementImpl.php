<?php
require_once dirname(__FILE__) . '/HTMLFormElementImpl.php';
require_once dirname(__FILE__) . '/../_interface/PageElement.php';
require_once dirname(__FILE__) . '/../_interface/Registrable.php';
require_once dirname(__FILE__) . '/JSONServerImpl.php';
require_once dirname(__FILE__) . '/JSONFunctionImpl.php';
require_once dirname(__FILE__) . '/JSONResponseImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 15:30
 */
class LoginFormulaPageElementImpl implements PageElement
{

    private $container;
    private $AJAXRegister;
    private $userLibrary;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->AJAXRegister = $container->getAJAXRegisterInstance();
        $this->userLibrary = $container->getUserLibraryInstance();
        $this->initialize();
    }

    private function initialize()
    {
        if ($this->userLibrary->getUserLoggedIn() !== null) {
            HTTPHeaderHelper::redirectToLocation("/");
        }
        if(count($this->userLibrary->listUsers()) == 0){
            $config = $this->container->getConfigInstance();
            $owner = $config->getOwner();
            $user = new UserImpl($owner['username'], $this->container->getDBInstance());
            $user->setMail($owner['mail']);
            $user->setPassword("password");
            $user->create();
            $user->getUserPrivileges()->addRootPrivileges();
        }
    }

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function getContent()
    {
        if ($this->evaluate($message, $status)) {
            return "<div class='notion $status'>$message</div>";
        }
        return "";
    }


    public function evaluate(&$message = "", &$status = "")
    {
        if (!isset($_POST['username'], $_POST['password'])) {
            return false;
        }

        /** @var $u User */
        if (($u = $this->userLibrary->getUserLoggedIn()) != null) {
            $u->logout();
        }

        $username = trim($_POST['username']);
        $user = $this->userLibrary->getUser($username);
        if ($user == null) {
            /** @var $u User */
            foreach ($this->userLibrary as $u) {
                if ($u->getMail() == $username) {
                    $user = $u;
                }


            }


        }
        if ($user == null) {
            $message = "Ugyldigt brugernavn";
            $status = HTMLFormElement::NOTION_TYPE_ERROR;
            return true;
        }
        $password = trim($_POST['password']);
        if (!$user->login($password)) {
            $message = "Ugyldigt kodeord";
            $status = HTMLFormElement::NOTION_TYPE_ERROR;
        } else {
            $message = "Du er nu logget ind";
            $status = HTMLFormElement::NOTION_TYPE_SUCCESS;
        }
        return true;
    }

}
