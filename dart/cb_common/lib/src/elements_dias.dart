part of elements;


class ElementDiasDecoration {

  Element _currentElement;

  Animation _currentAnimation;

  int _currentPosition = 0;

  final UListElement element;

  StreamController<int> _changeIndexController = new StreamController<int>();

  Stream<int> _changeIndexStream;

  Duration duration = new Duration(milliseconds:750);

  ElementDiasDecoration(this.element, [int startIndex = 0]) {

    _currentElement = element.children[Math.min(startIndex, element.children.length - 1)];
    _currentPosition = _calculatePosition(_currentElement);
    _updatePosition();
  }


  void _updatePosition(){
    element.style.left = "${_currentPosition}px";
  }

  int _calculatePosition(Element elm) {
    var e = element.children.first;
    var p = 0;
    while (e != null && e != elm) {
      p -= e.clientWidth;
      e = e.nextElementSibling;
    }

    return p;

  }


  int get currentIndex => element.children.indexOf(_currentElement);

  set currentIndex(int i) {
    i = i % numberOfElements;
    var newElement = element.children[i];
    var newPosition = _calculatePosition(newElement);

    if(_currentAnimation != null){
      _currentAnimation.stop();
    }

    _currentAnimation = new Animation(duration, (num pct){
      _currentPosition = Animation.easeInOutExpo(_currentPosition, newPosition - _currentPosition, pct);
      _updatePosition();
    }, (bool success){
      if(!success){
        return;
      }
      _currentPosition = newPosition;
      _updatePosition();
    });
    _currentAnimation.start();
    _currentElement = newElement;
    _changeIndexController.add(i);
  }

  void next() {
    currentIndex++;
  }

  void prev() {
    currentIndex--;
  }

  int get numberOfElements => element.children.length;

  Stream<int> get onIndexChange => _changeIndexStream == null?_changeIndexStream = _changeIndexController.stream.asBroadcastStream(): _changeIndexStream;

}
