<?php
namespace ChristianBudde\cbweb\controller\ajax;
use ChristianBudde\cbweb\BackendSingletonContainer;

use ChristianBudde\cbweb\model\updater\Updater;
use ChristianBudde\cbweb\test\JSONResponseImplTest;
use ChristianBudde\cbweb\util\file\File;
use ChristianBudde\cbweb\util\file\FileImpl;
use ChristianBudde\cbweb\controller\function_string\ParserImpl;
use ChristianBudde\cbweb\util\file\ImageFileImpl;
use ChristianBudde\cbweb\controller\json\JSONFunction;
use ChristianBudde\cbweb\controller\json\Response;
use ChristianBudde\cbweb\controller\json\ResponseImpl;
use ChristianBudde\cbweb\util\mail\Mail;
use ChristianBudde\cbweb\util\mail\MailImpl;
use ChristianBudde\cbweb\model\page\Page;
use ChristianBudde\cbweb\model\page\PageContent;
use ChristianBudde\cbweb\model\page\PageOrder;
use ChristianBudde\cbweb\model\user\User;
use ChristianBudde\cbweb\model\user\UserLibrary;
use ChristianBudde\cbweb\util\traits\ValidationTrait;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/1/14
 * Time: 8:23 PM
 */
class BackendTypeHandlerImpl implements TypeHandler
{

    use ValidationTrait;

    private $backend;
    private $userLibrary;


    private $sitePrivilegesFunction;

    private $userLoggedInAuthFunction;


    function __construct(BackendSingletonContainer $backend)
    {
        $this->backend = $backend;
        $this->userLibrary = $backend->getUserLibraryInstance();
        $this->sitePrivilegesFunction = function () {
            $currentUser = $this->userLibrary->getUserLoggedIn();
            if ($currentUser == null) {
                return false;
            }
            $privileges = $currentUser->getUserPrivileges();
            return $privileges->hasSitePrivileges();

        };

        $this->userLoggedInAuthFunction = function(){
            return $this->userLibrary->getUserLoggedIn() != null;
        };
    }


    /**
     * Sets up the type handler for provided type.
     * This should be called for each registered type.
     * @param Server $server The server which is setting-up the handler
     * @param string $type The type currently being set-up
     * @return void
     */
    public function setUp(Server $server, $type)
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

        // Setup mail
        $this->setupMailDomainLibraryHandler($server);
        $this->setupMailDomainHandler($server);
        $this->setupMailAddressLibraryHandler($server);
        $this->setupMailAddressHandler($server);
        $this->setupMailMailboxHandler($server);

        $this->setUpParserHandler($server);

        $this->setUpFileHandler($server);

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
     * @param \ChristianBudde\cbweb\controller\json\JSONFunction $function
     * @param mixed $instance
     * @return bool
     */
    public function canHandle($type, JSONFunction $function, $instance = null)
    {
        return false;
    }

    /**
     * @param string $type
     * @param \ChristianBudde\cbweb\controller\json\JSONFunction $function
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

    private function setUpUserLibraryHandler(Server $server)
    {

        $server->registerHandler($userLibraryHandler = new GenericObjectTypeHandlerImpl($this->userLibrary, 'UserLibrary'));


        $userLibraryHandler->addAuthFunction(function ($type, $instance, $functionName) {
            if ($this->userLibrary->getUserLoggedIn() == null &&
                $functionName != "userLogin" &&
                $functionName != "forgotPassword"
            ) {
                return false;
            }
            return true;
        });

        $userLibraryHandler->whitelistFunction('UserLibrary',
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

        $userLibraryHandler->addGetInstanceFunction('UserLibrary');

        $userLibraryHandler->addFunction("UserLibrary", "userLogin", function (UserLibrary $instance, $username, $password) {

            if (($user = $instance->getUser($username)) == null && $this->validMail($username)) {
                foreach($instance->listUsers() as $u){
                    if($user != null){
                        continue;
                    }
                    if($u->getMail() !== trim($username)){
                        continue;
                    }
                    if(!$u->verifyLogin($password)){
                        continue;
                    }
                    $user = $u;

                }


            }

            if($user == null){
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_LOGIN);
            }

            if ($user->login($password)) {
                return $instance->getUserSessionToken();

            }
            return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_LOGIN);

        });


        $userLibraryHandler->addFunctionAuthFunction('UserLibrary', 'deleteUser', function ($type, UserLibrary $instance, $functionName, $args) {
            return $this->isChildOfUser($args[0]);
        });

        $userLibraryHandler->addFunctionAuthFunction('UserLibrary', 'createUserFromMail', function ($type, UserLibrary $instance, $functionName, $args) {
            $privileges = $this->userLibrary->getUserLoggedIn()->getUserPrivileges();
            if ($privileges->hasRootPrivileges()) {
                return true;
            }

            if ($privileges->hasSitePrivileges() && $args[1] != "root") {
                return true;
            }

            return false;

        });

        $userLibraryHandler->addFunction("UserLibrary", "createUserFromMail", function (UserLibrary $instance, $mail, $privileges) {


            if (!$this->userLibrary->getUserLoggedIn()->isValidMail($mail)) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_MAIL);
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
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR);
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

        $userLibraryHandler->addFunction('UserLibrary', 'forgotPassword', function(UserLibrary $instance, $mail){

            $mail = trim($mail);
            if(!$this->validMail($mail)){
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_MAIL);
            }
            $domain = $this->backend->getConfigInstance()->getDomain();

            foreach($instance->listUsers() as $user){
                if($user->getMail() == $mail){
                    $password = uniqid();
                    $user->setPassword($password);
                    $m = new MailImpl();
                    $m->addReceiver($user);
                    $m->setSender("no-reply@$domain");
                    $m->setMailType(Mail::MAIL_TYPE_PLAIN);
                    $m->setSubject("Kodeord nulstillet");
                    $m->setMessage("Hej,\n" .
                        "Dit kodeord på $domain er blevet nulstillet.\n" .
                        "Du kan nu logge ind med følgende oplysninger:\n\n" .

                        "    Brugernavn: {$user->getUsername()}\n" .
                        "    Kodeord:    $password\n\n" .

                        "Vh\n" .
                        "Admin Jensen");
                    $m->sendMail();

                }
            }

            return new ResponseImpl();
        });

    }

    private function setUpUserHandler(Server $server)
    {

        $server->registerHandler($userHandler =
                new GenericObjectTypeHandlerImpl(($u = $this->userLibrary->getUserLoggedIn()) == null ? "ChristianBudde\\cbweb\\model\\user\\User" : $u),
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

        $userHandler->addTypeAuthFunction('User', function ($type, $instance, $functionName, $args) {
            return substr($functionName, 0, 3) != "set" || $this->userLibrary->getUserLoggedIn() === $instance;
        });

        $userHandler->addFunctionAuthFunction('User', 'delete', function ($type, $instance) {
            return $this->isChildOfUser($instance);
        });

        $userHandler->addFunction('User', 'setPassword', function (User $user, $oldPassword, $newPassword) {
            if (!$user->verifyLogin($oldPassword)) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_WRONG_PASSWORD);
            }

            if (!$user->setPassword($newPassword)) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_PASSWORD);
            }
            return new ResponseImpl();
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

    private function setUpPageOrderHandler(Server $server)
    {
        $server->registerHandler($pageOrderHandler = new GenericObjectTypeHandlerImpl($this->backend->getPageOrderInstance()), 'PageOrder');
        $pageOrderHandler->addGetInstanceFunction('PageOrder');

        $pageOrderHandler->addFunctionAuthFunction('PageOrder', 'deletePage', $this->sitePrivilegesFunction);
        $pageOrderHandler->addFunctionAuthFunction('PageOrder', 'deactivatePage', $this->sitePrivilegesFunction);
        $pageOrderHandler->addFunctionAuthFunction('PageOrder', 'setPageOrder', $this->sitePrivilegesFunction);
        $pageOrderHandler->addFunctionAuthFunction('PageOrder', 'createPage', $this->sitePrivilegesFunction);

        $pageOrderHandler->addFunction('PageOrder', 'createPage', function(PageOrder $pageOrder, $title){
            if (strlen($title) == 0) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_INVALID_PAGE_TITLE);
            }
            $id = strtolower($title);
            $id = $baseId = str_replace(' ', '_', $id);
            $id = $baseId = preg_replace('/[^a-z0-9\-_]/', '', $id);
            $i = 2;
            while (($p = $pageOrder->createPage($id)) === false) {
                $id = $baseId . "_" . $i;
                $i++;
            }
            $p->setTitle($title);
            $p->setTemplate('_main');

            return $p;
        });

    }

    private function setUpPageHandler(Server $server)
    {
        $server->registerHandler($pageHandler = new GenericObjectTypeHandlerImpl($this->backend->getPageOrderInstance()->getCurrentPage()), 'Page');
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

        $pagePrivilegesFunction = function ($type, Page $instance) {
            $currentUser = $this->userLibrary->getUserLoggedIn();
            if ($currentUser == null) {
                return false;
            }
            return $currentUser->getUserPrivileges()->hasPagePrivileges($instance);

        };

        $pageHandler->addFunctionAuthFunction('Page', 'setID', $pagePrivilegesFunction);
        $pageHandler->addFunctionAuthFunction('Page', 'setTitle', $pagePrivilegesFunction);
        $pageHandler->addFunctionAuthFunction('Page', 'setTemplate', $pagePrivilegesFunction);
        $pageHandler->addFunctionAuthFunction('Page', 'setAlias', $pagePrivilegesFunction);
        $pageHandler->addFunctionAuthFunction('Page', 'modify', $pagePrivilegesFunction);

        $pageHandler->addFunctionAuthFunction('Page', 'delete', $this->sitePrivilegesFunction);
        $pageHandler->addFunctionAuthFunction('Page', 'hide', $this->sitePrivilegesFunction);
        $pageHandler->addFunctionAuthFunction('Page', 'show', $this->sitePrivilegesFunction);

        $pageHandler->addGetInstanceFunction("Page");

    }

    private function setUpLoggerHandler(Server $server)
    {
        $server->registerHandler($logHandler = new GenericObjectTypeHandlerImpl($this->backend->getLoggerInstance()), 'Logger');
        $logHandler->addAuthFunction(function () {
            return $this->userLibrary->getUserLoggedIn() != null;
        });

        $logHandler->addFunctionAuthFunction("Logger", 'clearLog', $this->sitePrivilegesFunction);
        $logHandler->addFunctionAuthFunction("Logger", 'listLog', $this->sitePrivilegesFunction);
        $logHandler->addFunctionAuthFunction("Logger", 'getContextAt', $this->sitePrivilegesFunction);
    }

    private function setUpPageContentHandler(Server $server)
    {
        $server->registerHandler($contentHandler = new GenericObjectTypeHandlerImpl('ChristianBudde\cbweb\model\page\PageContent'));
        $contentHandler->addFunctionAuthFunction("PageContent", "addContent", function ($type, PageContent $instance) {
            return ($current = $this->backend->getUserLibraryInstance()->getUserLoggedIn()) != null && $current->getUserPrivileges()->hasPagePrivileges($instance->getPage());
        });
        $contentHandler->addGetInstanceFunction('PageContent');
    }

    private function setUpSiteContentHandler(Server $server)
    {
        $server->registerHandler($siteContentHandler = new GenericObjectTypeHandlerImpl('ChristianBudde\cbweb\model\site\SiteContent'));
        $siteContentHandler->addFunctionAuthFunction("SiteContent", "addContent", $this->sitePrivilegesFunction);
        $siteContentHandler->addGetInstanceFunction('SiteContent');
    }

    private function setUpPageContentLibraryHandler(Server $server)
    {
        $contentLibrary = $this->backend->getPageOrderInstance()->getCurrentPage()->getContentLibrary();
        $siteContentHandler = new GenericObjectTypeHandlerImpl($contentLibrary == null?"ChristianBudde\\cbweb\\model\\page\\PageContentLibrary":$contentLibrary);
        $server->registerHandler($siteContentHandler, "PageContentLibrary");
    }

    private function setUpSiteContentLibraryHandler(Server $server)
    {
        $server->registerHandler($siteContentHandler = new GenericObjectTypeHandlerImpl($this->backend->getSiteInstance()->getContentLibrary(), "SiteContentLibrary"));
    }

    private function setUpUpdaterHandler(Server $server)
    {
        $server->registerHandler($updaterHandler = new GenericObjectTypeHandlerImpl($this->backend->getUpdater(), "Updater"));
        $updaterHandler->addFunctionAuthFunction('Updater', 'update', $this->sitePrivilegesFunction);
        $updaterHandler->addFunctionAuthFunction('Updater', 'checkForUpdates', $this->sitePrivilegesFunction);
        $updaterHandler->addFunctionAuthFunction('Updater', 'allowCheckOnLogin', $this->sitePrivilegesFunction);
        $updaterHandler->addFunctionAuthFunction('Updater', 'disallowCheckOnLogin', $this->sitePrivilegesFunction);
        $updaterHandler->addFunctionAuthFunction('Updater', 'isCheckOnLoginAllowed', $this->sitePrivilegesFunction);

        $updaterHandler->addFunction('Updater', 'allowCheckOnLogin', function(Updater $instance){
            $user = $this->userLibrary->getUserLoggedIn();
            $instance->allowCheckOnLogin($user);
        });

        $updaterHandler->addFunction('Updater', 'disallowCheckOnLogin', function(Updater $instance){
            $user = $this->userLibrary->getUserLoggedIn();
            $instance->disallowCheckOnLogin($user);
        });

        $updaterHandler->addFunction('Updater', 'isCheckOnLoginAllowed', function(Updater $instance){
            $user = $this->userLibrary->getUserLoggedIn();
            return $instance->isCheckOnLoginAllowed($user);
        });
    }

    private function setUpArraysHandler(Server $server)
    {
        $handler = new ArrayAccessTypeHandlerImpl();
        $handler->addArray("POST", $_POST);
        $handler->addArray("GET", $_GET);
        $handler->addArray("FILES", $_FILES);
        $server->registerHandler($handler);
    }

    private function setUpFileHandler(Server $server)
    {
        $server->registerHandler($fileHandler = new GenericObjectTypeHandlerImpl('ChristianBudde\cbweb\util\file\ImageFile', 'File', 'ImageFile'));
        $fileHandler->whitelistFunction("File",
            'getContents',
            'getFilename',
            'getExtension',
            'getBasename',
            'size',
            'getDataURI',
            'getModificationTime',
            'getCreationTime',
            'getFile',
            'uploadFile',
            'getPath'
        );

        $fileHandler->whitelistFunction("ImageFile",
            'getFile',
            'uploadFile',
            'getWidth',
            'getHeight',
            'getRatio',
            'scaleToWidth',
            'scaleToHeight',
            'scaleToInnerBox',
            'scaleToOuterBox',
            'limitToInnerBox',
            'limitToOuterBox',
            'extendToInnerBox',
            'extendToOuterBox',
            'forceSize',
            'crop',
            'rotate',
            'mirrorVertical',
            'mirrorHorizontal'
        );

        $fileHandler->addFunctionPreCallFunction('ImageFile', 'crop', $f = $this->createSpliceAndTrueEndPreFunction(4));

        $fileHandler->addFunctionPreCallFunction('ImageFile', 'forceSize', $f = $this->createSpliceAndTrueEndPreFunction(2));
        $fileHandler->addFunctionPreCallFunction('ImageFile', 'scaleToInnerBox', $f);
        $fileHandler->addFunctionPreCallFunction('ImageFile', 'scaleToOuterBox', $f);
        $fileHandler->addFunctionPreCallFunction('ImageFile', 'limitToInnerBox', $f);
        $fileHandler->addFunctionPreCallFunction('ImageFile', 'limitToOuterBox', $f);
        $fileHandler->addFunctionPreCallFunction('ImageFile', 'extendToInnerBox', $f);
        $fileHandler->addFunctionPreCallFunction('ImageFile', 'extendToOuterBox', $f);

        $fileHandler->addFunctionPreCallFunction('ImageFile', 'scaleToWidth', $f = $this->createSpliceAndTrueEndPreFunction(1));
        $fileHandler->addFunctionPreCallFunction('ImageFile', 'scaleToHeight', $f);
        $fileHandler->addFunctionPreCallFunction('ImageFile', 'rotate', $f);

        $fileHandler->addFunctionPreCallFunction('ImageFile', 'mirrorHorizontal', $f = $this->createSpliceAndTrueEndPreFunction(0));
        $fileHandler->addFunctionPreCallFunction('ImageFile', 'mirrorVertical', $f);

        $library = $this->backend->getFileLibraryInstance();


        $fileHandler->addFunction('File', 'getPath', function (File $instance) use ($library) {
            return !$library->containsFile($instance)?null:$instance->getParentFolder()->getName()."/".$instance->getFilename();
        });

        $fileHandler->addFunction('File', 'getFile', function ($instance, $path) use ($library) {
            $f = new FileImpl($library->getFilesFolder()->getAbsolutePath() . "/$path");
            return $library->containsFile($f) ? $f : null;
        });

        $fileHandler->addFunction('ImageFile', 'getFile', function ($instance, $path) use ($library) {
            $f = new ImageFileImpl($library->getFilesFolder()->getAbsolutePath() . "/$path");
            return $library->containsFile($f) ? $f : null;
        });

        $fileHandler->addFunction('File', 'uploadFile', function ($instance, array $file) use ($library) {
            $f = $library->uploadToLibrary($this->userLibrary->getUserLoggedIn(), $file);
            return $f == null?null:$f->getParentFolder()->getName()."/".$f->getFilename();
        });

        $fileHandler->addFunction('ImageFile', 'uploadFile', function ($instance, array $file, array $sizes) use ($library) {
            $f = $library->uploadToLibrary($this->userLibrary->getUserLoggedIn(), $file);
            $f = new ImageFileImpl($f->getAbsoluteFilePath());

            $result = [];
            foreach($sizes as $key=>$val){
                if(!is_array($val) || !isset($val["height"], $val["width"], $val["scaleMethod"], $val["dataURI"])){
                    continue;
                }
                $width = $val["width"];
                $height = $val["height"];

                switch($val["scaleMethod"]){
                    case 0:
                        $newFile = $f->forceSize($width, $height, true);
                        break;
                    case 1:
                        $newFile = $f->scaleToWidth($width, true);
                        break;
                    case 2:
                        $newFile = $f->scaleToHeight($height, true);
                        break;
                    case 3:
                        $newFile = $f->scaleToInnerBox($width, $height, true);
                        break;
                    case 4:
                        $newFile = $f->scaleToOuterBox($width, $height, true);
                        break;
                    case 5:
                        $newFile = $f->limitToInnerBox($width, $height, true);
                        break;
                    case 6:
                        $newFile = $f->extendToInnerBox($width, $height, true);
                        break;
                    case 7:
                        $newFile = $f->limitToOuterBox($width, $height, true);
                        break;
                    case 8:
                        $newFile = $f->extendToOuterBox($width, $height, true);
                        break;
                    default:
                        $newFile = null;

                }
                if($newFile == null){
                    continue;
                }
                if($val["dataURI"]){
                    $result[$key] = $newFile->getDataURI();
                } else{
                    $result[$key] = $newFile->getParentFolder()->getName(). "/". $newFile->getFilename();
                }

            }
            $fp = $f == null?null:$f->getParentFolder()->getName()."/".$f->getFilename();
            return ["path"=>$fp, "sizes"=>$result];

        });

        $authFunction = function () {
            return $this
                ->backend
                ->getUserLibraryInstance()
                ->getUserLoggedIn()
                ->getUserPrivileges()
                ->hasPagePrivileges(
                    $this
                        ->backend
                        ->getPageOrderInstance()
                        ->getCurrentPage());
        };

        $fileHandler->addFunctionAuthFunction('File', 'uploadFile', $authFunction);


        $fileHandler->addTypeAuthFunction('ImageFile', function ($type, $instance, $function) use ($authFunction) {
            return
                !in_array($function, ['scaleToWidth',
                    'scaleToHeight',
                    'scaleToInnerBox',
                    'scaleToOuterBox',
                    'limitToInnerBox',
                    'limitToOuterBox',
                    'extendToInnerBox',
                    'extendToOuterBox',
                    'forceSize',
                    'crop',
                    'rotate',
                    'mirrorVertical',
                    'mirrorHorizontal',
                    'uploadFile']) ||
                $authFunction();
        });
    }

    private function createSpliceAndTrueEndPreFunction($length)
    {
        return function ($type, $instance, $functionName, &$arguments) use ($length) {
            $arguments = array_splice($arguments, 0, $length);
            $arguments[$length] = true;
        };
    }

    private function setUpParserHandler(Server $server)
    {
        $server->registerHandler($handler = new GenericObjectTypeHandlerImpl('Parser'));
        $handler->addFunction('Parser', 'parseJson', function($instance, $string){
            if(!is_string($string)){
                return $string;
            }
            return json_decode($string, true);
        });
        $handler->addFunction('Parser', 'parseFunctionStringArray', function($instance, $string){
            if(!is_string($string)){
                return $string;
            }
            $p = new ParserImpl();
            $p->parseArray($string, $result);
            return $result;
        });
    }

    private function setupMailDomainLibraryHandler(Server $server)
    {
        $handler = new GenericObjectTypeHandlerImpl($this->backend->getMailDomainLibraryInstance());
        $handler->addAlias('MailDomainLibrary', ['DomainLibrary']);
        $handler->whitelistType('MailDomainLibrary');
        $server->registerHandler($handler);
        $handler->addFunctionAuthFunction('MailDomainLibrary', 'deleteDomain', $this->sitePrivilegesFunction);
        $handler->addFunctionAuthFunction('MailDomainLibrary', 'createDomain', $this->sitePrivilegesFunction);
        $handler->addTypeAuthFunction('MailDomainLibrary', $this->userLoggedInAuthFunction);
    }

    private function setupMailDomainHandler(Server $server)
    {
        $handler = new GenericObjectTypeHandlerImpl('ChristianBudde\cbweb\model\mail\Domain');
        $handler->addAlias('MailDomain', ['Domain']);
        $handler->whitelistType('MailDomain');
        $handler->whitelistFunction('MailDomain',
            'getDomainName',
            'isActive',
            'activate',
            'deactivate',
            'getDescription',
            'setDescription',
            'lastModified',
            'getAddressLibrary',
            'isAliasDomain',
            'setAliasTarget',
            'getAliasTarget',
            'clearAliasTarget',
            'getDomainLibrary');
        $server->registerHandler($handler);

        $handler->addFunctionAuthFunction('MailDomainLibrary', 'deleteDomain', $this->sitePrivilegesFunction);
        $handler->addFunctionAuthFunction('MailDomainLibrary', 'createDomain', $this->sitePrivilegesFunction);
        $handler->addTypeAuthFunction('MailDomainLibrary', $this->userLoggedInAuthFunction);

    }

    private function setupMailAddressLibraryHandler(Server $server)
    {
    }

    private function setupMailAddressHandler(Server $server)
    {
    }

    private function setupMailMailboxHandler(Server $server)
    {
    }


}