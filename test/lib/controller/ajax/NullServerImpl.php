<?php
namespace ChristianBudde\Part\controller\ajax;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\Response;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/1/14
 * Time: 5:00 PM
 */

class NullServerImpl implements Server
{

    /**
     * Registers a AJAX type.
     * @param TypeHandler $type
     * @return void
     */
    public function registerHandler(TypeHandler $type)
    {
    }

    /**
     * Registers the handlers from config.
     * @return void
     */
    public function registerHandlersFromConfig()
    {
    }

    /**
     * @param string $input
     * @param string $token
     * @return Response
     */
    public function handleFromJSONString($input, $token = null)
    {
    }

    /**
     * @param string $input
     * @param string $token
     * @return Response
     */
    public function handleFromFunctionString($input, $token = null)
    {
    }

    /**
     * @param string $token
     * @return Response
     */
    public function handleFromRequestBody($token = null)
    {
    }
}