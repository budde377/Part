<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 11:21 AM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;
use ChristianBudde\Part\controller\ajax\Server;
use ChristianBudde\Part\controller\json\JSONFunction;

interface TypeHandler {

    /**
     * Sets up the type handler for provided type.
     * This should be called for each registered type.
     * @param Server $server The server which is setting-up the handler
     * @param String $type The type currently being set-up
     * @return void
     */
    public function setUp(Server $server, $type);

    /**
     * Lists the types that this handler can handle.
     * @return String[] An array of Strings
     */
    public function listTypes();


    /**
     * Checks if handler can handle. If so handle will be called with same arguments, else next suitable handler will be called.
     * @param String $type
     * @param JSONFunction $function
     * @param mixed $instance
     * @return bool
     */
    public function canHandle($type, JSONFunction $function, $instance = null);

    /**
     * @param String $type
     * @param \ChristianBudde\Part\controller\json\JSONFunction $function
     * @param mixed $instance
     * @return mixed
     */
    public function handle($type, JSONFunction $function, $instance = null);

    /**
     * Check if it has type
     * @param String $type
     * @return bool
     */
    public function hasType($type);
}