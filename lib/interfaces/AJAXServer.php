<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 11:20 AM
 */

interface AJAXServer {

    /**
     * Registers a AJAX type.
     * @param AJAXTypeHandler $type
     * @return void
     */

    public function registerHandler(AJAXTypeHandler $type);

    /**
     * Registers the handlers from config.
     * @return void
     */
    public function registerHandlersFromConfig();

    /**
     * @param string $input
     * @return string | null
     */
    public function handle($input);

    /**
     * @return string | null
     */
    public function handleFromRequestBody();
} 