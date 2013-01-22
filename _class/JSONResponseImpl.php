<?php
require_once dirname(__FILE__) . '/../_interface/JSONResponse.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 15:31
 * To change this template use File | Settings | File Templates.
 */
class JSONResponseImpl implements JSONResponse
{
    private $errorCode;
    private $errorMessage;
    private $type;
    private $id;
    /** @var JSONObject | String */
    private $payload;

    function __construct($type = JSONResponse::RESPONSE_TYPE_INFORMATION, $errorCode = null, $errorMessage = null)
    {
        $this->type = $type;
        $this->errorMessage = $errorMessage;
        $this->errorCode = $errorCode;
    }


    /**
     * @return string
     */
    public function getAsJSONString()
    {
        return json_encode($this->getAsArray());
    }

    /**
     * @return array
     */
    public function getAsArray()
    {
        $returnArray = array();
        $returnArray['type'] = $this->type;

        if($this->errorMessage != null){
            $returnArray['error_message'] = $this->errorMessage;
        }
        if($this->errorCode != null){
            $returnArray['error_code'] = $this->errorCode;
        }
        if($this->payload != null){
            if($this->payload instanceof JSONObject){
                $returnArray['payload'] = $this->payload->getAsArray();
            } else{
                $returnArray['payload'] = $this->payload;
            }
        }


        return $returnArray;
    }

    /**
     * @return String
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $payload
     * @return void
     */
    public function setPayload($payload)
    {
        if (!is_scalar($payload) && !($payload instanceof JSONObject)) {
            return;
        }

        $this->payload = $payload;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return String $id
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @param String $id
     * @return void
     */
    public function setID($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }


}
