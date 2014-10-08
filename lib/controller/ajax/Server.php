<?php
namespace ChristianBudde\cbweb\controller\ajax;
use ChristianBudde\cbweb\controller\json\Response;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 11:20 AM
 */

interface Server {

    /**
     * Registers a AJAX type.
     * @param TypeHandler $type
     * @return void
     */

    public function registerHandler(TypeHandler $type);

    /**
     * Registers the handlers from config.
     * @return void
     */
    public function registerHandlersFromConfig();

    /**
     * @param string $input
     * @return Response
     */
    public function handleFromJSONString($input);

    /**
     * @param string $input
     * @return Response
     */
    public function handleFromFunctionString($input);

    /**
     * @return Response
     */
    public function handleFromRequestBody();
} 