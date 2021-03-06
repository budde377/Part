library json;
import "dart:convert";
import "dart:html";
import "dart:async";
import 'core.dart';

part "src/json_client.dart";
part "src/json_object.dart";
part "src/json_response.dart";


/* Functions */

JSONObject parseObject(Map map) {
  if (map['type'] == null || map['type'] != 'object') {
    return null;
  }
  var name = map['name'];
  Map variables = map['variables'];
  if (name == null || !(variables is Map)) {
    return null;
  }
  var object = new JSONObject(name);
  variables.forEach((String key, value) {
    object.variables[key] = recursiveParsePayload(value);
  });

  return object;
}

JSONResponse parseResponse(Map map) {
  if (map['type'] == null || map['type'] != 'response') {
    return null;
  }
  var type;
  if ((type = map['response_type']) == null ||
  (type != Response.RESPONSE_TYPE_ERROR && type != Response.RESPONSE_TYPE_SUCCESS)) {
    return null;
  }


  if(type == Response.RESPONSE_TYPE_SUCCESS){
    var payload = recursiveParsePayload(map['payload']);
    return new JSONResponse.success(map['id'],payload);
  } else if(type == Response.RESPONSE_TYPE_ERROR){
    return new JSONResponse.error(map['error_code']);
  }

  return null;
}

dynamic recursiveParsePayload(payload){
  if(payload == null || payload is String || payload is bool || payload is num){
    return payload;
  }
  if(payload is Map){
    var p = parseObject(payload);
    if(p != null){
      return p;
    }
    var resultMap = <String,dynamic>{};
    payload.forEach((key,val){
      resultMap[key] = recursiveParsePayload(val);
    });
    return resultMap;
  } else if(payload is List){
    var resultList = <dynamic>[];
    payload.forEach((val){
      resultList.add(recursiveParsePayload(val));
    });
    return resultList;
  }
  return null;
}






