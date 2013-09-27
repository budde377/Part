part of elements;
/*

class ImageEditor {
  static final Map<ImageElement, ImageEditor> _cache = new Map<ImageElement, ImageEditor>();

  final CanvasElement canvas = new CanvasElement();

  final ImageElement image;

  int _height, _width, _canvasWidth, _canvasHeight, _originalWidth, _originalHeight;

  int _rotate = 0;

  int maxHeight, maxWidth, minWidth , minHeight ;

  double _zoom = 1.0;

  bool _mirrorVertical = false, _mirrorHorizontal = false;

  CanvasRenderingContext2D _context;

  factory ImageEditor(ImageElement img) => _cache.putIfAbsent(img, () => new ImageEditor._internal(img));

  ImageEditor._internal(this.image){
    _originalHeight = _height = _canvasHeight = canvas.height = image.clientHeight;
    _originalWidth = _width = _canvasWidth = canvas.width = image.clientWidth;
    minHeight = Math.min(_height, 40);
    minWidth = Math.min(_width, 40);
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
    var h = (_originalHeight * z).toInt(), w = (_originalWidth * z).toInt();

    if((maxHeight != null && h > maxHeight ) ||
       (maxWidth != null && w > maxWidth)){
      return;
    }

    if((minHeight != null && h < minHeight) ||
       (minWidth != null && w < minWidth)){
      return;
    }



    if (_isRotated) {
      _canvasHeight = w;
      _canvasWidth = h;
    } else {
      _canvasHeight = h;
      _canvasWidth = w;
    }
    _height = h;
    _width = w;
    _updateCanvasDimensions();
    _draw();
  }



  int get height => _height;

  set height(int h) => zoom = Math.max(h, 1) / _originalHeight;

  int get width => _width;

  set width(int w) => zoom = Math.max(1, w) / _originalWidth;

  num _degToRad(num deg) => deg * Math.PI / 180;

}
*/

class ImageEditProperties {

  ImageEditProperties(String url, {bool mirrorVertical:false, mirrorHorizontal:false, cropW:null, cropH:null, cropX:null, cropY:null, int rotate:0, width:null, height:null}){
    this.url = url;
    this.mirrorHorizontal = mirrorHorizontal;
    this.mirrorVertical = mirrorVertical;
    this.cropX = cropX;
    this.cropY = cropY;
    this.cropW = cropW;
    this.cropH = cropH;
    this.rotate = rotate;
    this.width = width;
    this.height = height;
  }

  ImageEditProperties.fromUrlString(String src) {
    var regexp = new RegExp(r"(_files/[^\-]+)(-[^\.]+)(\.[A-Za-z]+)$");
    var match = regexp.firstMatch(src);
    if (match == null) {
      return;
    }
    url = match[1] + match[3];
    var options = match[2];
    var optRegexp = new RegExp(r"-([CSRM])([^-]+)");
    optRegexp.allMatches(options).forEach((Match m) {
      var vars = m[2].split("_");
      vars.removeWhere((String s) => s.isEmpty);
      switch (m[1]) {
        case "S":
          width = int.parse(vars[0]);
          height = int.parse(vars[1]);
          break;
        case "C":
          cropX = int.parse(vars[0]);
          cropY = int.parse(vars[1]);
          cropW = int.parse(vars[2]);
          cropH = int.parse(vars[3]);
          break;
        case "R":
          rotate = int.parse(vars[0]);
          break;
        case "M":
          mirrorHorizontal = int.parse(vars[0]) > 0;
          mirrorVertical = int.parse(vars[1]) > 0;
          break;
      }

    });
  }

  ImageEditProperties.fromImageElement(ImageElement element): this.fromUrlString(element.src);

  bool mirrorVertical = false, mirrorHorizontal = false;

  int cropW, cropH, cropX, cropY, rotate = 0, width, height;

  String url;


}


class ImageEditor {
  static final Map<ImageElement, ImageEditor> _cache = new Map<ImageElement, ImageEditor>();

  final CanvasElement canvas = new CanvasElement();

  final ImageElement image;

  ImageElement _fullImage;

  int _rotate = 0;

  int _originalHeight, _originalWidth;

  int maxWidth, minWidth, maxHeight, minHeight;

  CanvasHandler _handler;

  CanvasLayer _cropLayer = new CanvasLayer(), _imageLayer = new CanvasLayer();

  ImageCanvasShape _image;

  ImageCropCanvasShape _cropShape;

  factory ImageEditor(ImageElement img) => _cache.putIfAbsent(img, () => new ImageEditor._internal(img));

  ImageEditor._internal(this.image){
    var properties = new ImageEditProperties.fromImageElement(image);
    if(properties.url == null){
      return;
    }
    _fullImage = new ImageElement(src:properties.url);
    _fullImage.onLoad.listen((_){
      _image = new ImageCanvasShape(_fullImage);
      _imageLayer.addShape(_image);
      _cropShape = new ImageCropCanvasShape(_image);
      _handler = new CanvasHandler(canvas);
      _originalWidth = _handler.width = image.clientWidth;
      _originalHeight = _handler.height = image.clientHeight;
      setProperties(properties);
      _handler.addLayer(_imageLayer);
      _cropLayer.addShape(_cropShape);

    });
  }


  void setProperties(ImageEditProperties properties) {
    if(_handler == null){
      return;
    }
    _handler.doWithoutUpdate(() {
      if (properties.rotate != null) {
        rotate = properties.rotate;
      }

      if (properties.width != null) {
        width = properties.width;
      }

      if (properties.height != null) {
        height = properties.height;
      }

      if (properties.cropX != null) {
        setCrop(properties.cropX / _handler.width, properties.cropY / _handler.height, properties.cropW / _handler.width, properties.cropH / _handler.height);
      } else {
        removeCrop();
      }
      mirrorHorizontal = properties.mirrorHorizontal;
      mirrorVertical = properties.mirrorVertical;
    });
    _handler.updateCanvas();
  }


  int get rotate => _rotate;

  bool get _isRotated => _rotate % 2 == 1;

  set rotate(int r) {
    r = r % 4;
    if (r == _rotate) {
      return;
    }

    _rotate = r;
    _image.rotationDeg = _cropShape.rotationDeg = 90 * r;
    var w = _handler.width;
    _handler.width = _handler.height;
    _handler.height = w;
    if (mirrorHorizontal != mirrorVertical) {
      _mirror(mirrorHorizontal, mirrorVertical);
    }
    if(maxWidth != null && _handler.width > maxWidth){
      width = maxWidth;
    }
    if(maxHeight != null && _handler.height > maxHeight){
      height = maxHeight;
    }
  }

  double get ratio => _originalWidth / _originalHeight;

  bool get mirrorVertical => _image.mirror == CanvasShape.MIRROR_VERTICAL || _image.mirror == CanvasShape.MIRROR_BOTH;

  set mirrorVertical(bool b) {
    if (b == mirrorVertical) {
      return;
    }
    _mirror(b, mirrorHorizontal);

  }

  bool get mirrorHorizontal => _image.mirror == CanvasShape.MIRROR_HORIZONTAL || _image.mirror == CanvasShape.MIRROR_BOTH;


  set mirrorHorizontal(bool b) {
    if (b == mirrorHorizontal) {
      return;
    }
    _mirror(mirrorVertical, b);

  }


  void _mirror(bool vertical, bool horizontal) {


    if (vertical && horizontal) {
      _image.mirror = CanvasShape.MIRROR_BOTH;
      return;
    }

    if (vertical) {
      _image.mirror = CanvasShape.MIRROR_VERTICAL;
      return;
    }

    if (horizontal) {
      _image.mirror = CanvasShape.MIRROR_HORIZONTAL;
      return;

    }
    _image.mirror = CanvasShape.NO_MIRROR;
  }


  int get height => _handler.height;

  set height(int h) {

    var w = (_isRotated ? _originalHeight / _originalWidth : ratio ) * h;
    _updateSize(w.toInt(), h);
  }

  int get width => _handler.width;

  set width(int w) {
    var h = (_isRotated ? ratio : _originalHeight / _originalWidth) * w;
    _updateSize(w, h.toInt());
  }


  void setCrop(double x, double y, double width, double height) {
    _cropShape.setCrop(x, y, width, height);

    if (!_cropLayer.handlerSet) {
      _handler.addLayer(_cropLayer);

    }


  }

  bool get hasCrop => _cropLayer.handlerSet;

  void _updateSize(int w, int h) {
    if ((maxHeight != null && h > maxHeight) || (minHeight != null && h < minHeight) || (maxWidth != null && maxWidth < w) || (minWidth != null && minWidth > w)) {
      return;
    }

    _handler.doWithoutUpdate(() {
      _image.width = _isRotated ? h : w;
      _image.height = _isRotated ? w : h;
      _handler.width = w;
      _handler.height = h;
    });
    _handler.updateCanvas();
  }


  void removeCrop() {
    if (_cropLayer == null) {
      return;
    }
    _cropLayer.remove();

  }


  int get cropX => _cropShape.cropX;

  int get cropY => _cropShape.cropY;

  int get cropW => _cropShape.cropW;

  int get cropH => _cropShape.cropH;

  num _interval(num n, num min, num max) => Math.max(min, Math.min(max, n));

  CanvasShape get dotNE => _cropShape.dotNE;

  CanvasShape get dotNW => _cropShape.dotNW;

  CanvasShape get dotSE => _cropShape.dotSE;

  CanvasShape get dotSW => _cropShape.dotSW;


}
