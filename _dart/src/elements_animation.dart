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

