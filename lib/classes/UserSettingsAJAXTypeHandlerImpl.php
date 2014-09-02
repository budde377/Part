<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/1/14
 * Time: 8:23 PM
 */

class UserSettingsAJAXTypeHandlerImpl implements AJAXTypeHandler{

    private $backend;

    function __construct(BackendSingletonContainer $backend)
    {
        $this->backend = $backend;
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
        $userLibrary = $this->backend->getUserLibraryInstance();
        $currentUser = $userLibrary->getUserLoggedIn();
        $server->registerHandler(new GenericObjectAJAXTypeHandlerImpl($userLibrary));
        $server->registerHandler(new GenericObjectAJAXTypeHandlerImpl($currentUser == null?"User":$currentUser));
        $server->registerHandler(new GenericObjectAJAXTypeHandlerImpl($this->backend->getPageOrderInstance()));
        $server->registerHandler(new GenericObjectAJAXTypeHandlerImpl($this->backend->getCurrentPageStrategyInstance()->getCurrentPage()));
        $server->registerHandler(new GenericObjectAJAXTypeHandlerImpl($this->backend->getLogInstance()));

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
}