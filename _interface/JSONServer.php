<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 10:00
 * To change this template use File | Settings | File Templates.
 */
interface JSONServer
{
    /**
     * This will evaluate a JSON string
     * @param String $jsonString
     * @return JSONResponse
     */
    public function evaluate($jsonString);

    /**
     * @param JSONFunction $function
     * @return void
     */
    public function registerJSONFunction(JSONFunction $function);

}
