<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 7:51 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\json\Response;
use ChristianBudde\Part\controller\json\ResponseImpl;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\model\user\UserLibrary;
use ChristianBudde\Part\util\mail\Mail;
use ChristianBudde\Part\util\mail\MailImpl;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;
use ChristianBudde\Part\util\traits\ValidationTrait;

//TODO test this

class UserLibraryTypeHandlerImpl extends GenericObjectTypeHandlerImpl
{

    use TypeHandlerTrait;
    use ValidationTrait;

    private $container;

    private $domain;

    function __construct(BackendSingletonContainer $container, UserLibrary $library)
    {
        parent::__construct($library, 'UserLibrary');
        $this->container = $container;
        $this->domain = $this->container->getConfigInstance()->getDomain();

        $this->whitelistFunction('UserLibrary',
            'listUsers',
            'deleteUser',
            'userLogin',
            'forgotPassword',
            'getUserLoggedIn',
            'getInstance',
            'getUser',
            'getParent',
            'getChildren',
            'createUserFromMail');


        $this->addFunctions();

        $this->addAuthFunctions();


    }

    private function checkLoginAuthFunction($type, UserLibrary $instance, $functionName)
    {
        if ($instance->getUserLoggedIn() == null &&
            $functionName != "userLogin" &&
            $functionName != "forgotPassword"
        ) {
            return false;
        }
        return true;
    }


    private function userLogin()
    {

        return function (UserLibrary $instance, $username, $password) {
            if (($user = $instance->getUser($username)) == null && $this->validMail($username)) {
                foreach ($instance->listUsers() as $u) {
                    if ($user != null) {
                        continue;
                    }
                    if ($u->getMail() !== $username) {
                        continue;
                    }
                    if (!$u->verifyLogin($password)) {
                        continue;
                    }
                    $user = $u;

                }
            }

            if ($user == null) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_LOGIN);
            }

            if ($user->login($password)) {
                return $instance->getUserSessionToken();

            }
            return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_LOGIN);
        };

    }

    private function createUserFromMailAuthFunction($type, UserLibrary $instance)
    {
        $args = func_get_arg(3);
        $privileges = $instance->getUserLoggedIn()->getUserPrivileges();
        if ($privileges->hasRootPrivileges()) {
            return true;
        }

        if ($privileges->hasSitePrivileges() && $args[1] != "root") {
            return true;
        }

        return false;

    }

    private function createUserFromMail()
    {
        return function (UserLibrary $instance, $mail, $privileges) {
            if (!$this->validMail($mail)) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_MAIL);
            }
            $username = $this->usernameFromMail($mail, $instance);
            $password = uniqid();

            if (!($user = $instance->createUser($username, $password, $mail, $instance->getUserLoggedIn()))) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR);
            }

            $this->assignUserPrivileges($privileges, $user);


            $this->sendMailToUser($user, "Du er blevet oprettet som bruger på {$this->domain}", "Hej,\n" .
                "Du er blevet oprettet som bruger på {$this->domain}.\n" .
                "Du kan logge ind med følgende oplysninger:\n\n" .

                "    Brugernavn: {$user->getUsername()}\n" .
                "    Kodeord:    $password\n\n" .

                "Vh\n" .
                "Admin Jensen");


            return $user;
        };
    }


    private function forgotPassword()
    {

        return function (UserLibrary $instance, $mail) {


            $mail = trim($mail);
            if (!$this->validMail($mail)) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_MAIL);
            }

            foreach ($instance->listUsers() as $user) {
                if ($user->getMail() == $mail) {
                    $password = uniqid();
                    $user->setPassword($password);

                    $this->sendMailToUser($user, "Kodeord nulstillet", "Hej,\n" .
                        "Dit kodeord på {$this->domain} er blevet nulstillet.\n" .
                        "Du kan nu logge ind med følgende oplysninger:\n\n" .

                        "    Brugernavn: {$user->getUsername()}\n" .
                        "    Kodeord:    $password\n\n" .

                        "Vh\n" .
                        "Admin Jensen");


                }
            }

            return new ResponseImpl();
        };
    }

    private function addFunctions()
    {
        $this->addGetInstanceFunction('UserLibrary');

        $this->addFunction("UserLibrary", "userLogin", $this->userLogin());

        $this->addFunction("UserLibrary", "createUserFromMail", $this->createUserFromMail());

        $this->addFunction('UserLibrary', 'forgotPassword', $this->forgotPassword());
    }

    private function addAuthFunctions()
    {
        $this->addFunctionAuthFunction('UserLibrary', 'deleteUser', function($type, UserLibrary $instance, $function, $args){
            return $this->isChildAuthFunction($args[0], $instance->getUserLoggedIn(), $instance);
        });

        $this->addFunctionAuthFunction('UserLibrary', 'createUserFromMail', $this->wrapFunction([$this, 'createUserFromMailAuthFunction']));

        $this->addAuthFunction($this->wrapFunction([$this, 'checkLoginAuthFunction']));

    }


    private function usernameFromMail($mail, UserLibrary $instance)
    {
        $username = explode('@', $mail);
        $username = $baseUsername = strtolower($username[0]);
        $post_fix = 2;
        while (!$instance->getUserLoggedIn()->isValidUsername($username)) {
            $username = $baseUsername . '_' . $post_fix;
            $post_fix++;
        }
        return $username;
    }

    private function assignUserPrivileges($privilegesString, User $user)
    {
        $privileges = $user->getUserPrivileges();
        if ($privilegesString == 'root') {
            $privileges->addRootPrivileges();
        } else if ($privilegesString == 'site') {
            $privileges->addSitePrivileges();
        }
    }

    private function sendMailToUser($user, $subject, $message)
    {
        $mail = new MailImpl();
        $mail->addReceiver($user);
        $mail->setSender("no-reply@{$this->domain}");
        $mail->setMailType(Mail::MAIL_TYPE_PLAIN);
        $mail->setSubject($subject);
        $mail->setMessage($message);
        $mail->sendMail();
    }


}