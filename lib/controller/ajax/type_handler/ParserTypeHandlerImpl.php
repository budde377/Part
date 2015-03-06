<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 6:38 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\controller\ajax\Server;
use ChristianBudde\Part\controller\function_string\ParserImpl;
use ChristianBudde\Part\controller\json\JSONFunction;

class ParserTypeHandlerImpl implements TypeHandler{

    /**
     * Sets up the type handler for provided type.
     * This should be called for each registered type.
     * @param Server $server The server which is setting-up the handler
     * @param String $type The type currently being set-up
     * @return void
     */
    public function setUp(Server $server, $type)
    {

    }

    /**
     * Lists the types that this handler can handle.
     * @return String[] An array of Strings
     */
    public function listTypes()
    {
        return ['Parser'];
    }

    /**
     * Checks if handler can handle. If so handle will be called with same arguments, else next suitable handler will be called.
     * @param String $type
     * @param JSONFunction $function
     * @param mixed $instance
     * @return bool
     */
    public function canHandle($type, JSONFunction $function, $instance = null)
    {
        if($type != 'Parser'){
            return false;
        }

        $name = $function->getName();
        if($name != 'parseJson' && $name != 'parseFunctionStringArray'){
            return false;
        }

        $args = $function->getArgs();

        if(count($args) < 1){
            return false;
        }

        if(!is_string($args[0])){
            return false;
        }

        return true;
    }

    /**
     * @param String $type
     * @param \ChristianBudde\Part\controller\json\JSONFunction $function
     * @param mixed $instance
     * @return mixed
     */
    public function handle($type, JSONFunction $function, $instance = null)
    {
        switch($function->getName()){
            case 'parseJson':
                return json_decode($function->getArg(0), true);

            case 'parseFunctionStringArray':
                return (new ParserImpl())->parseArrayString($function->getArg(0));
        }
        return false;
    }

    /**
     * Check if it has type
     * @param String $type
     * @return bool
     */
    public function hasType($type)
    {
        return $type == 'Parser';
    }
}