library json;
import "dart:json" as JSON;
import "dart:html";

part "src/json_client.dart";
part "src/json_function.dart";
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
    JSONObject o;
    if (value is num || value is String || value is bool || value == null) {
      object.variables[key] = value;
    } else if (value is Map && (o = parseObject(value)) != null) {
      object.variables[key] = o;
    }
  });

  return object;
}

JSONResponse parseResponse(Map map) {
  if (map['type'] == null || map['type'] != 'response') {
    return null;
  }
  var type;
  if ((type = map['response_type']) == null ||
  (type != RESPONSE_TYPE_ERROR && type != RESPONSE_TYPE_SUCCESS)) {
    return null;
  }

  var payload = recursiveParsePayload(map['payload']);
  return new JSONResponse(type, map['id'], payload, map['error_code']);

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






