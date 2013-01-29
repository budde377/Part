part of core;

class KeepAlive {
  String address;
  int interval;
  Timer _timer;
  HttpRequest _request;

  KeepAlive({String address: "/", int intervalSeconds:60}) {
    this.address = address;
    this.interval = intervalSeconds * 1000;
  }

  void start() {
    stop();
    Function callback;
    callback = (Timer timer) {
      request = new HttpRequest.get(address, (request) {});
      this._timer = new Timer(interval, callback);
    };

    _timer = new Timer(interval, callback);
  }

  void stop() {
    if (_timer != null) {
      _timer.cancel();
    }
  }
}