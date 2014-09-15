<?php
namespace ChristianBudde\cbweb\test\stub;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/1/14
 * Time: 5:00 PM
 */

class NullAJAXServerImpl implements \ChristianBudde\cbweb\controller\ajax\AJAXServer
{

    /**
     * Registers a AJAX type.
     * @param \ChristianBudde\cbweb\controller\ajax\AJAXTypeHandler $type
     * @return void
     */
    public function registerHandler(\ChristianBudde\cbweb\controller\ajax\AJAXTypeHandler $type)
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
     * @return \ChristianBudde\cbweb\controller\json\JSONResponse
     */
    public function handleFromJSONString($input)
    {
    }

    /**
     * @param string $input
     * @return \ChristianBudde\cbweb\controller\json\JSONResponse
     */
    public function handleFromFunctionString($input)
    {
    }

    /**
     * @return \ChristianBudde\cbweb\controller\json\JSONResponse
     */
    public function handleFromRequestBody()
    {
    }
}