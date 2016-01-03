<?php
namespace ChristianBudde\Part\controller\json;


/**
 * User: budde
 * Date: 22/01/13
 * Time: 15:31
 */
class ResponseImpl extends ElementImpl implements Response
{
    private $errorCode;
    private $type;
    private $id;
    /** @var Object | String */
    private $payload;

    function __construct($type = Response::RESPONSE_TYPE_SUCCESS, $errorCode = null)
    {
        $this->type = $type;
        $this->errorCode = $errorCode;
    }



    /**
     * @return array
     */
    public function getAsArray()
    {
        $returnArray = array();
        $returnArray['type'] = 'response';
        $returnArray['response_type'] = $this->type;
        $returnArray['error_code'] = $this->errorCode;
        $returnArray['payload'] = $this->payload;
        $returnArray['id'] = $this->id;

        return $returnArray;
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
        if (!$this->validValue($payload)) {
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
