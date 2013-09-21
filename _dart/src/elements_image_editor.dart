part of elements;


class ImageEditor {
  static final Map<ImageElement, ImageEditor> _cache = new Map<ImageElement, ImageEditor>();

  final CanvasElement canvas = new CanvasElement();

  final ImageElement image;

  int _height, _width, _canvasWidth, _canvasHeight, _originalWidth, _originalHeight;

  int _rotate = 0;

  double _zoom = 1.0;

  bool _mirrorVertical = false, _mirrorHorizontal = false;

  CanvasRenderingContext2D _context;

  factory ImageEditor(ImageElement img) => _cache.putIfAbsent(img, () => new ImageEditor._internal(img));

  ImageEditor._internal(this.image){
    _originalHeight = _height = _canvasHeight = canvas.height = image.clientHeight;
    _originalWidth = _width = _canvasWidth = canvas.width = image.clientWidth;
    _context = canvas.context2D;
    _draw();
  }

  void _draw() {
    _clear();
    _context.save();
    _context.translate(_canvasWidth / 2, _canvasHeight / 2);
    if (_isRotated) {
      _context.scale(_mirrorHorizontal ? -1 : 1, _mirrorVertical ? -1 : 1);
    } else {
      _context.scale(_mirrorVertical ? -1 : 1, _mirrorHorizontal ? -1 : 1);

    }
    _context.rotate(_degToRad(_rotate * 90));
    _context.translate(-_canvasWidth / 2, -_canvasHeight / 2);
    _context.drawImageScaled(image, (_canvasWidth - _width) / 2, (_canvasHeight - _height) / 2, _width, _height);
    _context.restore();
  }

  void _updateCanvasDimensions() {
    canvas.height = _canvasHeight;
    canvas.width = _canvasWidth;

  }

  num _abs(num n) => Math.sqrt(Math.pow(n, 2));

  void _clear() {
    var m = Math.max(_canvasWidth, _canvasHeight);
    _context.clearRect(0, 0, m, m);

  }


  int get rotate => _rotate;

  bool get _isRotated => _rotate % 2 == 1;

  set rotate(int r) {
    r = r % 4;
    if (r == _rotate) {
      return;
    }
    if (r % 2 != _rotate % 2) {
      var w = _canvasWidth;
      _canvasWidth = _canvasHeight;
      _canvasHeight = w;
      _updateCanvasDimensions();
    }
    _rotate = r;
    _draw();
  }

  double get ratio => _width / _height;

  bool get mirrorVertical => _mirrorVertical;

  set mirrorVertical(bool b) {
    _mirror(b, _mirrorHorizontal);
    _draw();
  }

  bool get mirrorHorizontal => _mirrorHorizontal;

  set mirrorHorizontal(bool b) {
    _mirror(_mirrorVertical, b);
    _draw();

  }


  void _mirror(bool vertical, bool horizontal) {
    if (vertical && horizontal) {
      _mirrorVertical = false;
      _mirrorHorizontal = false;
      rotate += 2;
      return;
    }
    _mirrorVertical = vertical;
    _mirrorHorizontal = horizontal;

  }


  double get zoom => _zoom;


  set zoom(double z) {
    _zoom = z;
    if (_isRotated) {
      _width = _canvasHeight = (_originalWidth * z).toInt();
      _height = _canvasWidth = (_originalHeight * z).toInt();
    } else {
      _height = _canvasHeight = (_originalHeight * z).toInt();
      _width = _canvasWidth = (_originalWidth * z).toInt();
    }
    _updateCanvasDimensions();
    _draw();
  }

  int get height => _canvasHeight;

  set height(int h) => zoom = Math.max(h, 1) / (_isRotated ? _originalWidth : _originalHeight);

  int get width => _canvasWidth;

  set width(int w) => zoom = Math.max(1, w) / (_isRotated ? _originalHeight : _originalWidth);

  num _degToRad(num deg) => deg * Math.PI / 180;

}