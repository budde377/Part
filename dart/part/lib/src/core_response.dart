part of core;

class Response<V> {
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
  static const ERROR_CODE_INVALID_LOGIN = 26;

  static const ERROR_CODE_COULD_NOT_PARSE_RESPONSE = 100;
  static const ERROR_CODE_NO_CONNECTION = 101;
  static const ERROR_CODE_UNKNOWN_ERROR = 102;

  final String type;
  final int error_code;
  final V payload;

  Response.success([V payload = null]): this.type = Response.RESPONSE_TYPE_SUCCESS, this.payload = payload, this.error_code = 0;

  Response.error(this.error_code): this.type = Response.RESPONSE_TYPE_ERROR, this.payload = null;

}

class FutureResponse<K> implements Future<Response<K>> {

  final Future<Response<K>> future;

  FutureResponse(Future<Response<K>> this.future);

  factory FutureResponse.error(int error_code) => new FutureResponse.fromComputation(() => new Response<K>.error(error_code));

  factory FutureResponse.success([K payload=null]) => new FutureResponse.fromComputation(() => new Response<K>.success(payload));

  factory FutureResponse.fromComputation(computation()) => new FutureResponse<K>(new Future<Response<K>>(computation));


  static Future<List> wait(Iterable<Future> futures, {bool eagerError}) => Future.wait(futures, eagerError:eagerError);

  static Future forEach(Iterable input, f(element)) => Future.forEach(input, f);

  static Future doWhile(f()) => Future.doWhile(f);

  Future then(onValue(Response value), {Function onError}) => future.then(onValue, onError:onError);

  Future catchError(Function onError, {bool test(Object error)}) => future.catchError(onError, test:test);

  Future<Response> whenComplete(action()) => future.whenComplete(action);

  Stream<Response> asStream() => future.asStream();

  Future timeout(Duration timeLimit, {onTimeout()}) => future.timeout(timeLimit, onTimeout:onTimeout);

  FutureResponse thenResponse({Response onSuccess(Response<K> value), Response onError(Response<K> value)}) => new FutureResponse(then((Response<K> value) {
    if (onSuccess != null && value.type == Response.RESPONSE_TYPE_SUCCESS) {
      return onSuccess(value);
    }
    if (onError != null && value.type == Response.RESPONSE_TYPE_ERROR) {
      return onError(value);
    }
    return value;
  }));

}