part of json;


class JSONResponse {

  static const RESPONSE_TYPE_SUCCESS = "success";
  static const RESPONSE_TYPE_ERROR = "error";

  static const ERROR_CODE_NO_SUCH_FUNCTION = 1;
  static const ERROR_CODE_MISSING_ARGUMENT = 2;
  static const ERROR_CODE_MALFORMED_REQUEST = 3;
  static const ERROR_CODE_PAGE_NOT_FOUND = 4;
  static const ERROR_CODE_PAGE_ORDER_PARTIAL_SET = 5;
  static const ERROR_CODE_INVALID_PAGE_ID = 6;
  static const ERROR_CODE_INVALID_PAGE_ALIAS = 7;
  static const ERROR_CODE_UNAUTHORIZED = 8;
  static const ERROR_CODE_INVALID_PAGE_TITLE = 9;
  static const ERROR_CODE_INVALID_USER_NAME = 10;
  static const ERROR_CODE_USER_NOT_FOUND = 11;
  static const ERROR_CODE_INVALID_PRIVILEGES = 12;
  static const ERROR_CODE_INVALID_MAIL = 13;
  static const ERROR_CODE_WRONG_PASSWORD = 14;
  static const ERROR_CODE_INVALID_PASSWORD = 15;
  static const ERROR_CODE_CANT_DELETE_CURRENT_PAGE = 16;
  static const ERROR_CODE_NOT_IMPLEMENTED = 17;
  static const ERROR_CODE_CANT_EDIT_PAGE = 18;
  static const ERROR_CODE_INVALID_FILE = 19;
  static const ERROR_CODE_FILE_NOT_FOUND = 20;
  static const ERROR_CODE_COULD_NOT_CREATE_FILE = 21;
  static const ERROR_CODE_INVALID_NAME = 22;
  static const ERROR_CODE_INVALID_SUBJECT = 23;
  static const ERROR_CODE_INVALID_MESSAGE = 24;
  static const ERROR_CODE_INVALID_INPUT = 25;


  final String type;
  final int id;
  final int error_code;
  final dynamic payload;

  JSONResponse(String type, [int id = null, payload = null, int error_code = null]) :
  this.type = type, this.id = id, this.error_code = error_code, this.payload = payload;
}
