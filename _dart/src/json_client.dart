part of json;

abstract class JSONClient {
  String urlPrefix = "";

  Future<JSONResponse> callFunction(JSONFunction function, [void progress(double pct)]);
}


class AJAXJSONClient extends JSONClient {

  AJAXJSONClient();

  Future<JSONResponse> _setUpRequest(HttpRequest request) {
    var completer = new Completer();
    request.onReadyStateChange.listen((Event e) {
      if (request.readyState != 4) {
        return;
      }
      print(request.responseText);
      Map responseObject = JSON.parse(request.responseText);
      var response;
      if ((response = parseResponse(responseObject)) == null) {
        completer.completeError(new Exception("Couldn't parse response"));
      } else {
        completer.complete(response);
      }

    });
    return completer.future;
  }

  Future<JSONResponse> callFunction(JSONFunction function, [void progress(double pct)]) {
    print(function.jsonString);
    var request = new HttpRequest();
    var future = _setUpRequest(request);
    if (progress != null) {
      request.onLoad.listen((ProgressEvent evt) => progress(evt.loaded / evt.total));
    }

    request.open("POST", urlPrefix + "?ajax=${function.name}");
    print(urlPrefix + "?ajax=${function.name}");
    request.send(function.jsonString);
    return future;
  }

}