part of core;


class Connection {

  bool allow404Response = true;

  bool _hasConnection = true;

  Duration timeOut = new Duration(seconds:10);

  StreamController<bool> _onChangeStream = new StreamController<bool>();

  static final Connection _cached = new Connection._internal();

  Connection._internal(){
    var f;
    f = (_) {
      new Timer(timeOut, ()=>hostReachable().then(f));
    };
    f(true);
  }

  HttpRequest buildRequest() => _buildRequest();

  HttpRequest _buildRequest([Completer<bool> completer = null]) {
    var request = new HttpRequest();
    if(completer == null){
      completer = new Completer();
    }
    request.onReadyStateChange.listen((ProgressEvent evt) {
      if (completer.isCompleted) {
        return;
      }
      if ((request.status >= 200 && request.status < 300) || request.status == 304 || ( allow404Response && request.status == 404)) {
        _updateStatus(true);
        completer.complete(true);
      }
      if (request.readyState == 4) {
        _updateStatus(false);
        completer.complete(false);
      }

    });
    return request;
  }


  factory Connection() => _cached;

  bool get hasConnection => _hasConnection;

  Future<bool> hostReachable() {
    var completer = new Completer<bool>();
    // Handle IE and more capable browsers
    var xhr = _buildRequest(completer);


    xhr.open("HEAD", "?rand=" + new DateTime.now().millisecondsSinceEpoch.toString());
    try {
      xhr.send();
    } catch (_) {
      _updateStatus(false);
      completer.complete(false);
    }
    return completer.future;
  }

  void _updateStatus(bool b) {
    if (b == _hasConnection) {
      return;
    }

    _hasConnection = b;
    _onChangeStream.add(b);

  }

  Stream<bool> get onHasConnectionChange => _onChangeStream.stream.asBroadcastStream();

}

Connection get connection => new Connection();