part of core;

class Animation {

  bool _exactHasBeenSeen = false, _run = false;

  double _startTime, _currentTime;

  int _duration;

  Function _animationFunction, _callbackFunction;

  Animation(Duration duration, void animationFunction(double pct), [void callback(bool success)]) {
    _animationFunction = animationFunction;
    _duration = duration.inMilliseconds;
    _callbackFunction = callback;
  }


  Animation start() {
    _run = true;
    window.requestAnimationFrame((time) {
      _startTime = time;
      _animate(time);
    });
    return this;
  }

  void _animate(double time) {
    _currentTime = time - _startTime;
    if (_currentTime <= _duration && _run) {
      _exactHasBeenSeen = _currentTime == _duration;
      _animationFunction(_currentTime / _duration);
      window.requestAnimationFrame(_animate);
    } else {
      if (!_exactHasBeenSeen && _run) {
        _animationFunction(1);
      }
      if (_callbackFunction != null) {
        _callbackFunction(_run);
      }
      stop();
    }

  }

  Animation stop() {
    _run = false;
    return this;
  }


}
