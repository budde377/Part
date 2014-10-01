<?php
namespace ChristianBudde\cbweb\test\stub;
use ChristianBudde\cbweb\controller\ajax\AJAXServer;
use ChristianBudde\cbweb\controller\ajax\AJAXTypeHandler;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/1/14
 * Time: 5:00 PM
 */

class NullAJAXServerImpl implements AJAXServer
{

    /**
     * Registers a AJAX type.
     * @param AJAXTypeHandler $type
     * @return void
     */
    public function registerHandler(AJAXTypeHandler $type)
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
     * @return \ChristianBudde\cbweb\controller\json\Response
     */
    public function handleFromJSONString($input)
    {
    }

    /**
     * @param string $input
     * @return \ChristianBudde\cbweb\controller\json\Response
     */
    public function handleFromFunctionString($input)
    {
    }

    /**
     * @return \ChristianBudde\cbweb\controller\json\Response
     */
    public function handleFromRequestBody()
    {
    }
}