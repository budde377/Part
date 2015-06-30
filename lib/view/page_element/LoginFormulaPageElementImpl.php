<?php
namespace ChristianBudde\Part\view\page_element;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\util\helper\HTTPHeaderHelper;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 15:30
 */
class LoginFormulaPageElementImpl extends PageElementImpl
{

    private $container;
    private $userLibrary;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->userLibrary = $container->getUserLibraryInstance();

    }

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        parent::generateContent();
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
            $status = "error";
            return true;
        }
        $password = trim($_POST['password']);
        if (!$user->login($password)) {
            $message = "Ugyldigt kodeord";
            $status = "error";
        } else {
            $message = "Du er nu logget ind";
            $status = "success";
        }
        return true;
    }

    /**
     * Will set up the page element.
     * If you want to ensure that you register some files, this would be the place to do this.
     * This should always be called before generateContent, at the latest right before.
     * @return void
     */
    public function setUpElement()
    {
        parent::setUpElement();
        if ($this->userLibrary->getUserLoggedIn() !== null) {
            HTTPHeaderHelper::redirectToLocation("/");
        }
        if(count($this->userLibrary->listUsers()) == 0){
            $config = $this->container->getConfigInstance();
            $owner = $config->getOwner();
            $user = $this->userLibrary->createUser($owner['username'], "password", $owner['mail']);
            $user->getUserPrivileges()->addRootPrivileges();
        }
    }
}
