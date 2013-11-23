part of elements;


class MoveBackgroundHandler {

  static const int MOVE_BACKGROUND_X = 1;

  static const int MOVE_BACKGROUND_Y = 2;

  static const int MOVE_BACKGROUND_BOTH = 3;

  static final Map<Element, MoveBackgroundHandler> _cache = new Map<Element, MoveBackgroundHandler>();

  final Element element;

  final DivElement background = new DivElement(), button_container = new DivElement();

  final ButtonElement move_button = new ButtonElement();

  final ButtonElement save_button = new ButtonElement();

  factory MoveBackgroundHandler(Element element) => _cache.putIfAbsent(element, () => new MoveBackgroundHandler._internal(element));

  String _orig_position;

  int _moveMode = MOVE_BACKGROUND_BOTH;

  Stream<MoveBackgroundHandler> _change_stream;
  StreamController<MoveBackgroundHandler> _change_stream_controller = new StreamController<MoveBackgroundHandler>();

  MoveBackgroundHandler._internal(this.element){
    element.classes.add('movable_background');
    background.classes.add('background_container');
    element.append(background);
    button_container.classes.add('move_button_container');
    move_button.classes.add('move_button');
    save_button.classes.add('save_move');
    save_button.hidden = true;
    button_container..append(move_button)..append(save_button);
    element.append(button_container);

    _change_stream = _change_stream_controller.stream.asBroadcastStream();

    _setUpListeners();
  }

  int get moveMode => _moveMode;

  set moveMode(int mode) {
    _moveMode = Math.min(Math.max(MOVE_BACKGROUND_X, mode), MOVE_BACKGROUND_BOTH);
  }

  void _setUpListeners() {
    move_button.onClick.listen((_) => _enableMove());
    save_button.onClick.listen((_) => _disableMove());
    var mouseMoveListener;

    background.onMouseDown.listen((_) {
      if(mouseMoveListener != null){
        mouseMoveListener.cancel();
      }
      bodySelectManager.disableSelect();
      mouseMoveListener = document.onMouseMove.listen((MouseEvent evt) {
        var x = evt.movement.x;
        var y = evt.movement.y;
        var computedStyle = element.getComputedStyle();
        switch (_moveMode) {
          case MOVE_BACKGROUND_BOTH:
            var currentX = int.parse(computedStyle.backgroundPositionX);
            var currentY = int.parse(computedStyle.backgroundPositionY);
            element.style.backgroundPosition = background.style.backgroundPosition = "${currentX + x}px ${currentY + y}px";
            break;
          case MOVE_BACKGROUND_Y:
            var currentX = computedStyle.backgroundPositionX;
            var currentY = int.parse(computedStyle.backgroundPositionY.replaceAll("px",""));
            element.style.backgroundPosition = background.style.backgroundPosition = "${currentX} ${currentY + y}px";
            break;
          case MOVE_BACKGROUND_X:
            var currentX = int.parse(computedStyle.backgroundPositionX.replaceAll("px",""));
            var currentY = computedStyle.backgroundPositionY;
            element.style.backgroundPosition = background.style.backgroundPosition = "${currentX+x}px ${currentY}";

            break;
        }
      });
    });
    document.onMouseUp.listen((_) {
      if (mouseMoveListener == null) {
        return;
      }
      bodySelectManager.enableSelect();

      mouseMoveListener.cancel();
      mouseMoveListener = null;
    });


  }

  void _enableMove() {
    save_button.hidden = !(move_button.hidden = true);
    var computedStyle = element.getComputedStyle();
    background.style.background = computedStyle.background;
    background.classes.add('active');
    _orig_position = computedStyle.backgroundPosition;
  }

  void _disableMove() {
    save_button.hidden = !(move_button.hidden = false);
    background.classes.remove('active');
    if(element.getComputedStyle().backgroundPosition != _orig_position){
      _change_stream_controller.add(this);
    }

  }

  Stream<MoveBackgroundHandler> get onChange => _change_stream;

}