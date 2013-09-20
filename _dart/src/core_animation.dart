part of core;

class Animation {

  bool _exactHasBeenSeen = false, _run = false;

  double _startTime, _currentTime;

  int _duration;

  Function _animationFunction, _callbackFunction;

  Animation(Duration duration, void animationFunction(double pct), [void callback(bool success)]) {
    this._animationFunction = animationFunction;
    this._duration = duration.inMilliseconds;
    this._callbackFunction = callback;
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


abstract class PropertyAnimation {
  final Element element;

  final String property;

  bool running;

  bool removePropertyOnComplete = false;

  PropertyAnimation(this.element, this.property);

  void animateTo(String to, {String from, Duration duration, void onComplete()});

  void stop();
}

class PxPropertyAnimation extends PropertyAnimation {

  int _fromm, _too;

  Animation _animation;

  PxPropertyAnimation(Element element, String property) : super(element, property);

  void animateTo(String to, {String from, Duration duration, void onComplete()}) {
    from = from == null ? element.getComputedStyle().getPropertyValue(property) : from;
    if (from == "auto" || to == "auto") {
      return;
    }
    stop();

    _fromm = parsePx(from);
    _too = parsePx(to);
    running = true;

    _animation = new Animation(duration == null ? new Duration(milliseconds:100) : duration, (double pct) {
      element.style.setProperty(property, "${linearAnimationFunction(pct, _fromm, _too).toInt()}px");
    }, (bool b) {

      running = false;
      if (!b) {
        return;
      }

      if (removePropertyOnComplete) {
        element.style.removeProperty(property);
      }
      if (onComplete == null) {
        return;
      }
      onComplete();
    });
    _animation.start();
  }

  void stop() {
    if (_animation == null) {
      return;
    }
    _animation.stop();
  }

}

class HeightPropertyAnimation extends PxPropertyAnimation {
  HeightPropertyAnimation(Element element) : super(element, "height");
}


class WidthPropertyAnimation extends PxPropertyAnimation {
  WidthPropertyAnimation(Element element) : super(element, "width");
}


num linearAnimationFunction(double pct, num from, num to) => from + (to - from) * pct;

int parsePx(String pxString) => int.parse(pxString.replaceAll(new RegExp("[^0-9]"), ""), onError:(_) => 0);

int maxChildrenHeight(Element element) {
  var largestSeen = 0;
  element.children.forEach((Element elm) => largestSeen = Math.max(largestSeen, elm.offsetTop + elm.offsetHeight));
  return largestSeen;
}