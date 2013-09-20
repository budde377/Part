part of json;
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
const ERROR_CODE_INVALID_USER_MAIL = 13;
const ERROR_CODE_WRONG_PASSWORD = 14;
const ERROR_CODE_INVALID_PASSWORD = 15;
const ERROR_CODE_CANT_DELETE_CURRENT_PAGE = 16;
const ERROR_CODE_NOT_IMPLEMENTED = 17;
const ERROR_CODE_CANT_EDIT_PAGE = 18;
const ERROR_CODE_INVALID_FILE = 19;



class JSONResponse {


  final String type;
  final int id;
  final int error_code;
  final dynamic payload;

  JSONResponse(String type, [int id = null, payload = null, int error_code = null]) :
  this.type = type, this.id = id, this.error_code = error_code, this.payload = payload;
}
