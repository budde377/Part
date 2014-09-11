<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/1/14
 * Time: 5:00 PM
 */

class NullAJAXServerImpl implements ChristianBudde\cbweb\AJAXServer{

    /**
     * Registers a AJAX type.
     * @param ChristianBudde\cbweb\AJAXTypeHandler $type
     * @return void
     */
    public function registerHandler(ChristianBudde\cbweb\AJAXTypeHandler $type)
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
     * @return ChristianBudde\cbweb\JSONResponse
     */
    public function handleFromJSONString($input)
    {
    }

    /**
     * @param string $input
     * @return ChristianBudde\cbweb\JSONResponse
     */
    public function handleFromFunctionString($input)
    {
    }

    /**
     * @return ChristianBudde\cbweb\JSONResponse
     */
    public function handleFromRequestBody()
    {
    }
}