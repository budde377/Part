part of json;


class JSONResponse<V> extends Response<V>{

  final int id;

  JSONResponse.success([int id = null, V payload = null]) : this.id = id, super.success(payload);
  JSONResponse.error([int id = null, int error_code]) : this.id = id, super.error(error_code);

}
