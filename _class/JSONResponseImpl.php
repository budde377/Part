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
    private $type;
    private $id;
    /** @var JSONObject | String */
    private $payload;

    function __construct($type = JSONResponse::RESPONSE_TYPE_SUCCESS, $errorCode = null)
    {
        $this->type = $type;
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
        $returnArray['type'] = 'response';
        $returnArray['response_type'] = $this->type;

        if($this->errorCode != null){
            $returnArray['error_code'] = $this->errorCode;
        }
        if($this->payload != null){
            $returnArray['payload'] = $this->generatePayloadArray($this->payload);
        }
        if($this->id != null){
            $returnArray['id'] = $this->id;
        }

        return $returnArray;
    }

    private function generatePayloadArray($payload){
        if(is_scalar($payload)){
            return $payload;
        } else if($payload instanceof JSONObject){
            return $payload->getAsArray();
        } else {
            $returnArray = array();
            foreach($payload as $key=>$val){
                $returnArray[$key] = $this->generatePayloadArray($val);
            }
            return $returnArray;
        }
    }

    /**
     * @return String
     */
    public function getResponseType()
    {
        return $this->type;
    }

    /**
     * @param mixed $payload
     * @return void
     */
    public function setPayload($payload)
    {
        if(!$this->checkPayload($payload)){
            return;
        }

        $this->payload = $payload;
    }

    private function checkPayload($payload){
        if(is_scalar($payload) || $payload instanceof JSONObject){
            return true;
        } else if (is_array($payload)){
            $ok_array = true;
            foreach($payload as $val){
                $ok_array = $ok_array && $this->checkPayload($val);
            }
            return $ok_array;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return int | null
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @param int | null $id
     * @return void
     */
    public function setID($id)
    {
        $this->id = intval($id);
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }


}
