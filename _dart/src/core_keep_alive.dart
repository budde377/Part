part of core;

class KeepAlive {
  String address;
  Duration interval;
  Timer _timer;

  KeepAlive({String address: "/", int intervalSeconds:60}) {
    this.address = address;
    interval = new Duration(seconds:intervalSeconds);
  }

  void start() {
    stop();
    _timer = new Timer.periodic(interval, (Timer timer){HttpRequest.getString(address);});

  }

  void stop() {
    if (_timer != null) {
      _timer.cancel();
    }
  }
}