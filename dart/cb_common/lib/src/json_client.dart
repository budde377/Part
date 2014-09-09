part of json;

abstract class JSONClient {
  String urlPrefix = "";


  Future<JSONResponse> callFunctionString(String function, {void progress(double pct), FormData form_data:null});
}


class AJAXJSONClient extends JSONClient {

  static final AJAXJSONClient _cached = new AJAXJSONClient._internal();

  factory AJAXJSONClient() => _cached;

  AJAXJSONClient._internal();

  Future<JSONResponse> _setUpRequest(HttpRequest request) {
    var completer = new Completer();
    request.onReadyStateChange.listen((Event e) {
      if (request.readyState != 4) {
        return;
      }
      debug(request.responseText);
      Map responseObject = JSON.decode(request.responseText);
      var response;
      if ((response = parseResponse(responseObject)) == null) {
        completer.completeError(new Exception("Couldn't parse response"));
      } else {
        completer.complete(response);
      }

    });
    return completer.future;
  }

  Future<JSONResponse> callFunctionString(String function, {void progress(double pct), FormData form_data:null}) {
    var request = new HttpRequest();
    var future = _setUpRequest(request);
    _registerProgressHandler(request, progress);

    if(form_data != null){
      request.open("POST", urlPrefix + "?ajax=$function");
      request.send(form_data);
      debug("POST: "+urlPrefix + "?ajax=$function");
    } else {
      request.open("GET", urlPrefix + "?ajax=$function");
      debug("GET: "+urlPrefix + "?ajax=$function");
      request.send();
    }
    return future;
  }

  _registerProgressHandler(HttpRequest request, progress) {
    if (progress != null) {
      var f = (ProgressEvent evt) => evt.total == 0?0:progress(evt.loaded / evt.total);
      request.onLoad.listen(f);
      request.onLoadEnd.listen(f);
      request.onProgress.listen(f);
    }
  }


}




AJAXJSONClient get ajaxClient => new AJAXJSONClient();