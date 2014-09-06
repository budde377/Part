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
        $this->setUpPageOrderHandler($server);
        $this->setUpPageHandler($server);
        $this->setUpLoggerHandler($server);
        $this->setUpUpdaterHandler($server);

        $this->setUpPageContentHandler($server);
        $this->setUpPageContentLibraryHandler($server);
        $this->setUpSiteContentHandler($server);
        $this->setUpSiteContentLibraryHandler($server);

        $this->setUpArraysHandler($server);

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

        $userLibraryHandler->addAuthFunction(function ($type, $instance, $functionName) {
            if ($this->userLibrary->getUserLoggedIn() == null && $functionName != "userLogin") {
                return false;
            }
            return true;
        });

        $userLibraryHandler->whitelistFunction('UserLibrary',
            'listUsers',
            'deleteUser',
            'userLogin',
            'getUserLoggedIn',
            'getInstance',
            'getUser',
            'getParent',
            'getChildren',
            'createUserFromMail');

        $userLibraryHandler->addGetInstanceFunction('UserLibrary');

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
            "setPassword",
            "logout",
            "isValidMail",
            "isValidUsername",
            "isValidPassword",
            "delete",
            "getInstance");

        $userHandler->addGetInstanceFunction("User");

        $userHandler->addTypeAuthFunction('User',function ($type, $instance, $functionName, $args){
            return substr($functionName, 0,3) != "set" || $this->userLibrary->getUserLoggedIn() === $instance;
        });

        $userHandler->addFunctionAuthFunction('User','delete',function ($type, $instance){
            return  $this->isChildOfUser($instance);
        });

        $userHandler->addFunction('User','setPassword', function(User $user, $oldPassword, $newPassword){
            if(!$user->verifyLogin($oldPassword)){
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_WRONG_PASSWORD);
            }

            if(!$user->setPassword($newPassword)){
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_PASSWORD);
            }
            return new JSONResponseImpl();
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

    private function setUpPageOrderHandler(AJAXServer $server)
    {
        $server->registerHandler($pageOrderHandler = new GenericObjectAJAXTypeHandlerImpl($this->backend->getPageOrderInstance()), 'PageOrder');
        $pageOrderHandler->addGetInstanceFunction('PageOrder');

        $pageOrderHandler->addFunctionAuthFunction('PageOrder', 'deletePage', $this->sitePrivilegesFunction);
        $pageOrderHandler->addFunctionAuthFunction('PageOrder', 'deactivatePage', $this->sitePrivilegesFunction);
        $pageOrderHandler->addFunctionAuthFunction('PageOrder', 'setPageOrder', $this->sitePrivilegesFunction);
        $pageOrderHandler->addFunctionAuthFunction('PageOrder', 'createPage', $this->sitePrivilegesFunction);

    }

    private function setUpPageHandler(AJAXServer $server)
    {
        $server->registerHandler($pageHandler = new GenericObjectAJAXTypeHandlerImpl($this->backend->getPageOrderInstance()->getCurrentPage()), 'Page');
        $pageHandler->whitelistFunction('Page',
            'isHidden',
            'hide',
            'show',
            'getID',
            'getTitle',
            'getTemplate',
            'getAlias',
            'getContent',
            'setID',
            'setTitle',
            'setTemplate',
            'setAlias',
            'delete',
            'match',
            'isEditable',
            'isValidID',
            'isValidAlias',
            'lastModified',
            'modify',
            'getInstance'
        );

        $pagePrivilegesFunction = function ($type, Page $instance){
            $currentUser = $this->userLibrary->getUserLoggedIn();
            if($currentUser == null){
                return false;
            }
            return $currentUser->getUserPrivileges()->hasPagePrivileges($instance);

        };

        $pageHandler->addFunctionAuthFunction('Page','setID', $pagePrivilegesFunction);
        $pageHandler->addFunctionAuthFunction('Page','setTitle', $pagePrivilegesFunction);
        $pageHandler->addFunctionAuthFunction('Page','setTemplate', $pagePrivilegesFunction);
        $pageHandler->addFunctionAuthFunction('Page','setAlias', $pagePrivilegesFunction);
        $pageHandler->addFunctionAuthFunction('Page','modify', $pagePrivilegesFunction);

        $pageHandler->addFunctionAuthFunction('Page','delete', $this->sitePrivilegesFunction);
        $pageHandler->addFunctionAuthFunction('Page','hide', $this->sitePrivilegesFunction);
        $pageHandler->addFunctionAuthFunction('Page','show', $this->sitePrivilegesFunction);

        $pageHandler->addGetInstanceFunction("Page");

    }

    private function setUpLoggerHandler(AJAXServer $server)
    {
        $server->registerHandler($logHandler = new GenericObjectAJAXTypeHandlerImpl($this->backend->getLoggerInstance()), 'Logger');
        $logHandler->addAuthFunction(function() {
           return $this->userLibrary->getUserLoggedIn() != null;
        });
    }

    private function setUpPageContentHandler(AJAXServer $server)
    {
        $server->registerHandler($contentHandler = new GenericObjectAJAXTypeHandlerImpl( 'PageContent'));
        $contentHandler->addFunctionAuthFunction("PageContent", "addContent", function ($type, PageContent $instance){
            return ($current = $this->backend->getUserLibraryInstance()->getUserLoggedIn()) != null && $current->getUserPrivileges()->hasPagePrivileges($instance->getPage());
        });
        $contentHandler->addGetInstanceFunction('PageContent');
    }

    private function setUpSiteContentHandler(AJAXServer $server)
    {
        $server->registerHandler($siteContentHandler = new GenericObjectAJAXTypeHandlerImpl('SiteContent'));
        $siteContentHandler->addFunctionAuthFunction("SiteContent", "addContent", $this->sitePrivilegesFunction);
        $siteContentHandler->addGetInstanceFunction('SiteContent');
    }

    private function setUpPageContentLibraryHandler(AJAXServer $server)
    {
        $server->registerHandler($siteContentHandler = new GenericObjectAJAXTypeHandlerImpl($this->backend->getPageOrderInstance()->getCurrentPage()->getContentLibrary(), "PageContentLibrary"));
    }

    private function setUpSiteContentLibraryHandler(AJAXServer $server)
    {
        $server->registerHandler($siteContentHandler = new GenericObjectAJAXTypeHandlerImpl($this->backend->getSiteInstance()->getContentLibrary(), "SiteContentLibrary"));
    }

    private function setUpUpdaterHandler(AJAXServer $server)
    {
        $server->registerHandler($updaterHandler = new GenericObjectAJAXTypeHandlerImpl($this->backend->getUpdater(), "Updater"));
        $updaterHandler->addFunctionAuthFunction('Updater', 'update', $this->sitePrivilegesFunction);
        $updaterHandler->addFunctionAuthFunction('Updater', 'checkForUpdates', $this->sitePrivilegesFunction);
    }

    private function setUpArraysHandler(AJAXServer $server)
    {
        $handler = new ArrayAccessAJAXTypeHandlerImpl();
        $handler->addArray("POST", $_POST);
        $handler->addArray("GET", $_GET);
        $handler->addArray("FILES", $_FILES);
        $server->registerHandler($handler);
    }

}