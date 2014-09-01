<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/1/14
 * Time: 5:00 PM
 */

class NullAJAXServerImpl implements AJAXServer{

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
     * @return JSONResponse
     */
    public function handleFromJSONString($input)
    {
    }

    /**
     * @param string $input
     * @return JSONResponse
     */
    public function handleFromFunctionString($input)
    {
    }

    /**
     * @return JSONResponse
     */
    public function handleFromRequestBody()
    {
    }
}