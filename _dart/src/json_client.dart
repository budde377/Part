part of json;

abstract class JSONClient {
  callFunction(JSONFunction function, [void callback(JSONResponse response)]);
}


class AJAXJSONClient extends JSONClient {
  final String ajaxID;
  String urlPrefix = "";
  final Map<int, Function> pendingFunctions = new Map<int, Function>();

  static final Map<String, JSONClient> _cache = <String, JSONClient>{};
  factory AJAXJSONClient(String ajaxID){
    if (_cache.containsKey(ajaxID)) {
      return _cache[ajaxID];
    } else {
      var client = new AJAXJSONClient._internal(ajaxID);
      _cache[ajaxID] = client;
      return client;
    }
  }

  AJAXJSONClient._internal(this.ajaxID);

  void _setUpRequest(HttpRequest request) {
    request.onReadyStateChange.listen((Event e) {
      if (request.readyState != 4) {
        return;
      }
      print(request.responseText);
      Map responseObject = JSON.parse(request.responseText);
      var response;
      int id;
      var f;
      if ((response = parseResponse(responseObject)) == null
      || (id = response.id) == null
      || (f = pendingFunctions[id]) == null) {
        return;
      }
      f(response);

      pendingFunctions.remove(id);
    });
  }

  void callFunction(JSONFunction function, [void callback(JSONResponse response)]) {
    print(function.jsonString);
    if (callback != null) {
      pendingFunctions[function.id] = callback;
    }
    var request = new HttpRequest();
    _setUpRequest(request);
    request.open("POST", urlPrefix+"?ajax=$ajaxID");
    print(urlPrefix+"?ajax=$ajaxID");
    request.send(function.jsonString);
  }

}