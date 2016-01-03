<?php
namespace ChristianBudde\Part\controller\ajax\type_handler;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\Server;
use ChristianBudde\Part\controller\json\JSONFunction;
use ChristianBudde\Part\util\traits\ValidationTrait;
use SebastianBergmann\Exporter\Exception;

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



    function __construct(BackendSingletonContainer $backend)
    {
        $this->backend = $backend;

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
        $server->registerHandler($this->backend->getUserLibraryInstance()->generateTypeHandler());
        if(($user = $this->backend->getUserLibraryInstance()->getUserLoggedIn()) != null){
            $server->registerHandler($user->generateTypeHandler());
        }
        $server->registerHandler($this->backend->getPageOrderInstance()->generateTypeHandler());
        if(($page = $this->backend->getPageOrderInstance()->getCurrentPage()) != null){
            $server->registerHandler($page->generateTypeHandler());
        }
        $server->registerHandler($this->backend->getLoggerInstance()->generateTypeHandler());
        $server->registerHandler($this->backend->getUpdaterInstance()->generateTypeHandler());
        $server->registerHandler($this->backend->getSiteInstance()->generateTypeHandler());
        $server->registerHandler($this->backend->getFileLibraryInstance()->generateTypeHandler());
        $server->registerHandler(new PostGetFilesArrayAccessTypeHandlerImpl());
        $server->registerHandler(new ParserTypeHandlerImpl());




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
     * @param \ChristianBudde\Part\controller\json\JSONFunction $function
     * @param mixed $instance
     * @return bool
     */
    public function canHandle($type, JSONFunction $function, $instance = null)
    {
        return false;
    }

    /**
     * @param string $type
     * @param \ChristianBudde\Part\controller\json\JSONFunction $function
     * @param mixed $instance
     * @return mixed
     */
    public function handle($type, JSONFunction $function, $instance = null)
    {
        throw new Exception("Can't handle anything");
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