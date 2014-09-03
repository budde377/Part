<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/1/14
 * Time: 8:23 PM
 */
class BackendAJAXTypeHandlerImpl implements AJAXTypeHandler
{

    private $backend;
    private $userLibrary;


    private $sitePrivilegesFunction;

    function __construct(BackendSingletonContainer $backend)
    {
        $this->backend = $backend;
        $this->userLibrary = $backend->getUserLibraryInstance();
        $this->sitePrivilegesFunction  = function (){
            $currentUser = $this->userLibrary->getUserLoggedIn();
            if($currentUser == null){
                return false;
            }
            $privileges = $currentUser->getUserPrivileges();
            return $privileges->hasSitePrivileges();

        };
    }


    /**
     * Sets up the type handler for provided type.
     * This should be called for each registered type.
     * @param AJAXServer $server The server which is setting-up the handler
     * @param string $type The type currently being set-up
     * @return void
     */
    public function setUp(AJAXServer $server, $type)
    {


        $this->setUpUserLibraryHandler($server);
        $this->setUpUserHandler($server);
        $this->setUpPageOrderInstance($server);
        $server->registerHandler(new GenericObjectAJAXTypeHandlerImpl($this->backend->getCurrentPageStrategyInstance()->getCurrentPage()), 'Page');
        $server->registerHandler(new GenericObjectAJAXTypeHandlerImpl($this->backend->getLogInstance()), 'Log');

    }

    /**
     * Lists the types that this handler can handle.
     * @return array An array of strings
     */
    public function listTypes()
    {
        return [];
    }

    /**
     * Checks if handler can handle. If so handle will be called with same arguments, else next suitable handler will be called.
     * @param string $type
     * @param JSONFunction $function
     * @param mixed $instance
     * @return bool
     */
    public function canHandle($type, JSONFunction $function, $instance = null)
    {
        return false;
    }

    /**
     * @param string $type
     * @param JSONFunction $function
     * @param mixed $instance
     * @return mixed
     */
    public function handle($type, JSONFunction $function, $instance = null)
    {

    }

    /**
     * Check if it has type
     * @param string $type
     * @return bool
     */
    public function hasType($type)
    {
        return false;
    }

    private function setUpUserLibraryHandler(AJAXServer $server)
    {

        $server->registerHandler($userLibraryHandler = new GenericObjectAJAXTypeHandlerImpl($this->userLibrary, 'UserLibrary'));
        $userLibraryHandler->addAuthFunction(function ($type, $instance, $functionName, $args) {
            if ($this->userLibrary->getUserLoggedIn() == null && $functionName != "userLogin") {
                return false;
            }
            return true;
        });

        $userLibraryHandler->whitelistFunction('UserLibrary',
            'listUsers',
            'deleteUser',
            'getUserLoggedIn',
            'getUser',
            'getParent',
            'getChildren',
            'createUserFromMail');


        $userLibraryHandler->addFunction("UserLibrary", "userLogin", function (UserLibrary $instance, $username, $password) {
            if (($user = $instance->getUser($username)) == null) {
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_LOGIN);
            }

            if ($user->login($password)) {
                return $user;
            }
            return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_LOGIN);

        });



        $userLibraryHandler->addFunctionAuthFunction('UserLibrary', 'deleteUser', function ($type, UserLibrary $instance, $functionName, $args) {
            return $this->isChildOfUser($instance->getUser($args[0]));
        });

        $userLibraryHandler->addFunctionAuthFunction('UserLibrary', 'createUserFromMail', function ($type, UserLibrary $instance, $functionName, $args){
            $privileges = $this->userLibrary->getUserLoggedIn()->getUserPrivileges();
            if($privileges->hasRootPrivileges()){
                return true;
            }

            if($privileges->hasSitePrivileges() && $args[1] != "root"){
                return true;
            }

            return false;

        });

        $userLibraryHandler->addFunction("UserLibrary", "createUserFromMail", function (UserLibrary $instance, $mail, $privileges) {


            if (!$this->userLibrary->getUserLoggedIn()->isValidMail($mail)) {
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_MAIL);
            }
            $username = explode('@', $mail);
            $username = $baseUsername = strtolower($username[0]);
            $i = 2;
            while (!$this->userLibrary->getUserLoggedIn()->isValidUsername($username)) {
                $username = $baseUsername . '_' . $i;
                $i++;
            }
            $password = uniqid();

            if (!($user = $instance->createUser($username, $password, $mail, $this->userLibrary->getUserLoggedIn()))) {
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR);
            }
            $p = $user->getUserPrivileges();
            if ($privileges == 'root') {
                $p->addRootPrivileges();
            } else if ($privileges == 'site') {
                $p->addSitePrivileges();
            }
            // SEND MAIL TO USER
            $domain = $this->backend->getConfigInstance()->getDomain();
            $m = new MailImpl();
            $m->addReceiver($user);
            $m->setSender("no-reply@$domain");
            $m->setMailType(Mail::MAIL_TYPE_PLAIN);
            $m->setSubject("Du er blevet oprettet som bruger på $domain");
            $m->setMessage("Hej,\n" .
                "Du er blevet oprettet som bruger på $domain.\n" .
                "Du kan logge ind med følgende oplysninger:\n\n" .

                "    Brugernavn: {$user->getUsername()}\n" .
                "    Kodeord:    $password\n\n" .

                "Vh\n" .
                "Admin Jensen");
            $m->sendMail();

            return $user;

        });


    }

    private function setUpUserHandler(AJAXServer $server)
    {

        $server->registerHandler($userHandler =
            new GenericObjectAJAXTypeHandlerImpl(($u = $this->userLibrary->getUserLoggedIn()) == null ? "User" : $u),
            ' User');
        $userHandler->whitelistFunction("User",
            "getUsername",
            "getMail",
            "getLastLogin",
            "getParent",
            "getUserPrivileges",
            "getUniqueId",
            "setMail",
            "setUsername",
            "logout",
            "isValidMail",
            "isValidUsername",
            "isValidPassword",
            "getUserVariables",
            "delete");

        $userHandler->addTypeAuthFunction('User',function ($type, $instance, $functionName, $args){
            return substr($functionName, 0,3) != "set" || $this->userLibrary->getUserLoggedIn() === $instance;
        });

        $userHandler->addFunctionAuthFunction('User','delete',function ($type, $instance, $functionName, $args){
            return  $this->isChildOfUser($instance);
        });

    }

    /**
     * @param User $user
     * @return Callable
     */
    private function isChildOfUser(User $user)
    {
        return in_array($user, $this->userLibrary->getChildren($this->userLibrary->getUserLoggedIn()));
    }

    private function setUpPageOrderInstance(AJAXServer $server)
    {
        $server->registerHandler($pageOrderHandler = new GenericObjectAJAXTypeHandlerImpl($this->backend->getPageOrderInstance()), 'PageOrder');

        $pageOrderHandler->addFunctionAuthFunction('PageOrder', 'deletePage', $this->sitePrivilegesFunction);
        $pageOrderHandler->addFunctionAuthFunction('PageOrder', 'deactivatePage', $this->sitePrivilegesFunction);
        $pageOrderHandler->addFunctionAuthFunction('PageOrder', 'setPageOrder', $this->sitePrivilegesFunction);
        $pageOrderHandler->addFunctionAuthFunction('PageOrder', 'createPage', $this->sitePrivilegesFunction);

    }

}