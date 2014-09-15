<?php
namespace ChristianBudde\cbweb\controller\json;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 12:01 PM
 */

interface JSONParser {



    /**
     * @param string $input
     * @return JSONElement
     */
    public function parse($input);

    /**
     * @return JSONElement
     */
    public function parseFromRequestBody();

} 