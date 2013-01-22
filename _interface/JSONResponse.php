<?php
require_once dirname(__FILE__).'/JSONElement.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 10:02
 * To change this template use File | Settings | File Templates.
 */
interface JSONResponse extends JSONElement
{
    const RESPONSE_TYPE_SUCCESS = "success";
    const RESPONSE_TYPE_INFORMATION = "information";
    const RESPONSE_TYPE_ERROR = "error";

    const ERROR_CODE_NO_SUCH_METHOD = 1;
    const ERROR_WRONG_PARAMETERS = 2;
    const ERROR_CODE_MALFORMED_REQUEST = 3;


    /**
     * @return String
     */
    public function getType();


    /**
     * @return mixed
     */
    public function getPayload();

    /**
     * @param mixed $payload
     * @return void
     */
    public function setPayload($payload);


    /**
     * @return String $id
     */
    public function getID();

    /**
     * @param String $id
     * @return void
     */
    public function setID($id);

    /**
     * @return int
     */
    public function getErrorCode();

    /**
     * @return string
     */
    public function getErrorMessage();

}
