<?php
require_once dirname(__FILE__).'/../_interface/JSONServer.php';
require_once dirname(__FILE__).'/../_class/JSONResponseImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 23/01/13
 * Time: 10:18
 * To change this template use File | Settings | File Templates.
 */
class JSONServerImpl implements JSONServer
{
    private $functions = array();

    private function stripPrefix($string){
        $prefAr = explode('.',$string);
        return is_array($prefAr) && count($prefAr) > 1?$prefAr[1]:$string;
    }



    /**
     * This will evaluate a JSON string
     * @param String $jsonString
     * @return JSONResponse
     */

    public function evaluate($jsonString)
    {
        $jsonArray = json_decode($jsonString,true);
        $id = isset($jsonArray['id'])?$jsonArray['id']:null;
        if(!isset($jsonArray['type'],$jsonArray['name'],$jsonArray['args']) ||
            !is_array($jsonArray['args']) ||
            $jsonArray['type'] !=  "function"){
            $result = new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR,JSONResponse::ERROR_CODE_MALFORMED_REQUEST);
            $result->setID($id);
            return $result;
        }
        $functionName = $this->stripPrefix($jsonArray['name']);
        if(!isset($this->functions[$functionName])){
            $result =  new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR,JSONResponse::ERROR_CODE_NO_SUCH_FUNCTION);
            $result->setID($id);
            return $result;
        }
        /** @var $function JSONFunction */
        $function = $this->functions[$functionName];
        $args = $jsonArray['args'];
        $missingArguments = false;
        $endArgs = array();
        foreach($function->getArgs() as $key=>$arg){
            $missingArguments = $missingArguments || !array_key_exists($arg, $args);
            if(!$missingArguments){
                $endArgs[$key] = $args[$arg];
            }
        }
        if($missingArguments){
            $result = new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR,JSONResponse::ERROR_CODE_MISSING_ARGUMENT);
            $result->setID($id);
            return $result;
        }
        $result = $function->call($endArgs);
        if($result instanceof JSONResponse){
            $result->setID($id);
        }
        return $result;
    }

    /**
     * @param JSONFunction $function
     * @return void
     */
    public function registerJSONFunction(JSONFunction $function)
    {
        $this->functions[$function->getName()] = $function;
    }

    /**
     * This will evaluate php://input
     * @return JSONResponse
     */
    public function evaluatePostInput()
    {
        $postData = file_get_contents("php://input");
        return $this->evaluate($postData);
    }
}
