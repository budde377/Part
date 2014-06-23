<?php
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
    const RESPONSE_TYPE_ERROR = "error";

    const ERROR_CODE_NO_SUCH_FUNCTION = 1;
    const ERROR_CODE_MISSING_ARGUMENT = 2;
    const ERROR_CODE_MALFORMED_REQUEST = 3;
    const ERROR_CODE_PAGE_NOT_FOUND = 4;
    const ERROR_CODE_PAGE_ORDER_PARTIAL_SET = 5;
    const ERROR_CODE_INVALID_PAGE_ID = 6;
    const ERROR_CODE_INVALID_PAGE_ALIAS = 7;
    const ERROR_CODE_UNAUTHORIZED = 8;
    const ERROR_CODE_INVALID_PAGE_TITLE = 9;
    const ERROR_CODE_INVALID_USER_NAME = 10;
    const ERROR_CODE_USER_NOT_FOUND = 11;
    const ERROR_CODE_INVALID_PRIVILEGES = 12;
    const ERROR_CODE_INVALID_MAIL = 13;
    const ERROR_CODE_WRONG_PASSWORD = 14;
    const ERROR_CODE_INVALID_PASSWORD = 15;
    const ERROR_CODE_CANT_DELETE_CURRENT_PAGE = 16;
    const ERROR_CODE_NOT_IMPLEMENTED = 17;
    const ERROR_CODE_CANT_EDIT_PAGE = 18;
    const ERROR_CODE_INVALID_FILE = 19;
    const ERROR_CODE_FILE_NOT_FOUND = 20;
    const ERROR_CODE_COULD_NOT_CREATE_FILE = 21;
    const ERROR_CODE_INVALID_NAME = 22;
    const ERROR_CODE_INVALID_SUBJECT = 23;
    const ERROR_CODE_INVALID_MESSAGE = 24;
    const ERROR_CODE_INVALID_INPUT = 25;
    const ERROR_CODE_INVALID_LOGIN = 26;

    /**
     * @return String
     */
    public function getResponseType();


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
     * @return int | null
     */
    public function getID();

    /**
     * @param int | null $id
     * @return void
     */
    public function setID($id);

    /**
     * @return int
     */
    public function getErrorCode();


}
