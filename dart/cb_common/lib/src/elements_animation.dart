part of elements;



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

  core.Animation _animation;

  PxPropertyAnimation(Element element, String property) : super(element, property);

  void animateTo(String to, {String from, Duration duration, void onComplete()}) {
    from = from == null ? element.getComputedStyle().getPropertyValue(property) : from;
    if (from == "auto" || to == "auto") {
      return;
    }
    stop();

    _fromm = core.parseNumber(from);
    _too = core.parseNumber(to);
    running = true;

    _animation = new core.Animation(duration == null ? new Duration(milliseconds:100) : duration, (double pct) {
      element.style.setProperty(property, "${core.linearAnimationFunction(pct, _fromm, _too).toInt()}px");
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


class ScrollAnimation{

  static ScrollAnimation _cache;

  core.Animation _scrollAnimation;

  factory ScrollAnimation() => _cache == null?_cache = new ScrollAnimation._internal():_cache;

  ScrollAnimation._internal();

  Future<bool> scrollToPosition(Point<int> p, {num animationFunction(num start, num end, num pct), Duration duration}){
    var completer = new Completer<bool>();
    duration = (duration == null)?new Duration(milliseconds:500):duration;
    animationFunction = animationFunction == null?core.Animation.linearTween:animationFunction;
    if(_scrollAnimation != null){
      _scrollAnimation.stop();
    }
    var y = window.scrollY;
    var c = Math.min(p.y, core.body.scrollHeight-window.innerHeight)-y;

    _scrollAnimation = new core.Animation(duration, (num pct){
      window.scrollTo(window.scrollX,animationFunction(y, c, pct).toInt());
    }, completer.complete);
    _scrollAnimation.start();
    return completer.future;
  }

  Future<bool> scrollToElement(Element elm, {num animationFunction(num start, num end, num pct), Duration duration}) =>
    scrollToPosition(elm.documentOffset, animationFunction: animationFunction, duration : duration);

}

ScrollAnimation get scroll => new ScrollAnimation();