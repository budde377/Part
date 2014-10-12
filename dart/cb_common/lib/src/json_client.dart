part of json;

abstract class JSONClient {
  String urlPrefix = "";


  Future<Response> callFunctionString(String function, {void progress(double pct), FormData form_data:null});
}


class AJAXJSONClient extends JSONClient {

  static final AJAXJSONClient _cached = new AJAXJSONClient._internal();

  factory AJAXJSONClient() => _cached;

  AJAXJSONClient._internal();

  Future<Response> _setUpRequest(HttpRequest request) {
    var completer = new Completer();
    request.onReadyStateChange.listen((Event e) {
      if (request.readyState != 4) {
        return;
      }
      debug(request.responseText);
      var responseObject;
      try {
        responseObject = JSON.decode(request.responseText);
      } catch(e){
        completer.complete(new Response.error(connection.hasConnection?Response.ERROR_CODE_COULD_NOT_PARSE_RESPONSE:Response.ERROR_CODE_NO_CONNECTION));
        return;
      }
      var response;
      if ((response = parseResponse(responseObject)) == null) {
        completer.complete(new Response.error(connection.hasConnection?Response.ERROR_CODE_COULD_NOT_PARSE_RESPONSE:Response.ERROR_CODE_NO_CONNECTION));
      } else {
        completer.complete(response);
      }

    });
    return completer.future;
  }

  Future<Response> callFunctionString(String function, {void progress(num pct), FormData form_data:null}) {
    if(!connection.hasConnection){
      return new Future(()=>new Response.error(Response.ERROR_CODE_NO_CONNECTION));
    }

    var request = connection.buildRequest();
    var future = _setUpRequest(request);
    _registerProgressHandler(request, progress);
    var token = window.localStorage['user-login-token'];
    token = token != null?"&token="+token:"";
    if(form_data != null){
      request.open("POST", urlPrefix + "?ajax=$function$token");
      request.send(form_data);
      debug("POST: "+urlPrefix + "?ajax=$function$token");
    } else {
      request.open("GET", urlPrefix + "?ajax=$function$token");
      debug("GET: "+urlPrefix + "?ajax=$function$token");
      request.send();
    }
    return future;
  }

  _registerProgressHandler(HttpRequest request, progress) {
    if (progress != null) {
      var f = (ProgressEvent evt) => evt.total == 0?0:progress(evt.loaded / evt.total);
      request.upload.onProgress.listen(f);
      request.upload.onLoadEnd.listen(f);
    }
  }


}




AJAXJSONClient get ajaxClient => new AJAXJSONClient();