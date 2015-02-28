<?php
namespace ChristianBudde\Part\test\stub;
use ChristianBudde\Part\controller\ajax\Server;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\JSONFunction;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 3:46 PM
 */

class StubAJAXTypeHandlerImpl implements TypeHandler
{

    public $calledMethods = [];
    public $types = [];
    public $handle = [];
    public $canHandle = [];

    function __construct()
    {

        if (!isset($_SESSION['type_handlers'])) {
            $_SESSION['type_handlers'] = [];

        }
        $_SESSION['type_handlers'][] = $this;

        $this->calledMethods[] = ['method' => '__construct', 'arguments' => func_get_args()];

    }

    /**
     * Sets up the type handler for provided type.
     * This should be called for each registered type.
     * @param \ChristianBudde\Part\controller\ajax\Server $server The server which is setting-up the handler
     * @param string $type The type currently being set-up
     * @return void
     */
    public function setUp(Server $server, $type)
    {
        $this->calledMethods[] = ['method' => 'setUp', 'arguments' => func_get_args()];
    }

    /**
     * Lists the types that this handler can handle.
     * @return array An array of strings
     */
    public function listTypes()
    {
        $this->calledMethods[] = ['method' => 'listTypes', 'arguments' => func_get_args()];
        return $this->types;
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
        $this->calledMethods[] = ['method' => 'canHandle', 'arguments' => func_get_args()];
        return $this->canHandle[$type];
    }

    /**
     * @param string $type
     * @param \ChristianBudde\Part\controller\json\JSONFunction $function
     * @param mixed $instance
     * @return mixed
     */
    public function handle($type, JSONFunction $function, $instance = null)
    {
        $this->calledMethods[] = ['method' => 'handle', 'arguments' => func_get_args()];

        return $this->handle[$type];

    }

    /**
     * Check if it has type
     * @param string $type
     * @return bool
     */
    public function hasType($type)
    {
        $this->calledMethods[] = ['method' => 'hasType', 'arguments' => func_get_args()];
    }
}