part of core;


class Animation {

  bool _exactHasBeenSeen = false, _run = false;

  double _startTime, _currentTime;

  int _duration;

  Function _animationFunction, _callbackFunction;

  Animation(Duration duration, void animationFunction(num pct), [void callback(bool success)]) {
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


  static num linearTween(num start, num change, num pct) => pct * change + start;

  static num easeInQuad(num start, num change, num pct) => pct * pct * change + start;

  static num easeOutQuad(num start, num change, num pct) => -change * pct * (pct - 2) + start;

  static num easeInOutQuad(num start, num change, num pct) {
    pct *= 2;
    if (pct < 1) {
      return change / 2 * pct * pct + start;
    }
    pct--;
    return -change / 2 * (pct * (pct - 2) - 1) + start;
  }

  static num easeInCubic(num start, num change, num pct) => change * pct * pct * pct + start;

  static num easeOutCubic(num start, num change, num pct) {
    pct--;
    return change * (pct * pct * pct + 1) + start;
  }


  static num easeInOutCubic(num start, num change, num pct) {
    pct *= 2;
    if (pct < 1) {
      return change / 2 * pct * pct * pct + start;
    }
    pct -= 2;
    return change / 2 * (pct * pct * pct + 2) + start;
  }

  static num easeInQuart(num start, num change, num pct) => change * pct * pct * pct * pct + start;

  static num easeOutQuart(num start, num change, num pct) {
    pct--;
    return -change * (pct * pct * pct * pct - 1) + start;
  }

  static num easeInOutQuart(num start, num change, num pct) {
    pct *= 2;
    if (pct < 1) return change / 2 * pct * pct * pct * pct + start;
    pct -= 2;
    return -change / 2 * (pct * pct * pct * pct - 2) + start;
  }

  static num easeInQuint(num start, num change, num pct) => change * pct * pct * pct * pct * pct + start;

  static num easeOutQuint(num b, num c, num t) {
    t--;
    return c * (t * t * t * t * t + 1) + b;
  }

  static num easeInOutQuint(num b, num c, num t) {
    t *= 2;
    if (t < 1) return c / 2 * t * t * t * t * t + b;
    t -= 2;
    return c / 2 * (t * t * t * t * t + 2) + b;
  }

  static num easeInSine(num b, num c, num t) => -c * Math.cos(t * (Math.PI / 2)) + c + b;

  static num easeInOutSine(num b, num c, num t) => -c / 2 * (Math.cos(Math.PI * t) - 1) + b;

  static num easeInExpo(num b, num c, num t) => c * Math.pow(2, 10 * (t - 1)) + b;

  static num easeOutExpo(num b, num c, num t) => c * ( -Math.pow(2, -10 * t) + 1 ) + b;


  static num easeInOutExpo(num b, num c, num t) {
    t *= 2;
    if (t < 1) return c / 2 * Math.pow(2, 10 * (t - 1)) + b;
    t--;
    return c / 2 * ( -Math.pow(2, -10 * t) + 2 ) + b;
  }


  static num easeInCirc(num b, num c, num t) => -c * (Math.sqrt(1 - t * t) - 1) + b;

  static num easeOutCirc(num b, num c, num t) {
    t--;
    return c * Math.sqrt(1 - t * t) + b;
  }


  static num easeInOutCirc(num b, num c, num t) {
    t *= 2;
    if (t < 1) return -c / 2 * (Math.sqrt(1 - t * t) - 1) + b;
    t -= 2;
    return c / 2 * (Math.sqrt(1 - t * t) + 1) + b;
  }

}