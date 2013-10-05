part of elements;

class ImageEditProperties {

  ImageEditProperties(String url, {bool mirrorVertical:false, mirrorHorizontal:false, cropW:null, cropH:null, cropX:null, cropY:null, int rotate:0, width:null, height:null}) {
    this._url = url;
    this._mirrorHorizontal = mirrorHorizontal;
    this._mirrorVertical = mirrorVertical;
    this._cropX = cropX;
    this._cropY = cropY;
    this._cropW = cropW;
    this._cropH = cropH;
    this._rotate = rotate;
    this._width = width;
    this._height = height;
  }

  ImageEditProperties.fromUrlString(String src) {
    var regexp = new RegExp(r"(_files/[^\-]+)(-[^\.]+)(\.[A-Za-z]+)$");
    var match = regexp.firstMatch(src);
    if (match == null) {
      return;
    }
    _url = match[1] + match[3];
    var options = match[2];
    var optRegexp = new RegExp(r"-([CSRM])([^-]+)");
    optRegexp.allMatches(options).forEach((Match m) {
      var vars = m[2].split("_");
      vars.removeWhere((String s) => s.isEmpty);
      switch (m[1]) {
        case "S":
          _width = int.parse(vars[0]);
          _height = int.parse(vars[1]);
          break;
        case "C":
          _cropX = int.parse(vars[0]);
          _cropY = int.parse(vars[1]);
          _cropW = int.parse(vars[2]);
          _cropH = int.parse(vars[3]);
          break;
        case "R":
          _rotate = int.parse(vars[0]);
          break;
        case "M":
          _mirrorHorizontal = int.parse(vars[0]) > 0;
          _mirrorVertical = int.parse(vars[1]) > 0;
          break;
      }

    });
  }

  ImageEditProperties.fromImageElement(ImageElement element): this.fromUrlString(element.src);

  bool _mirrorVertical = false, _mirrorHorizontal = false;

  int _cropW, _cropH, _cropX, _cropY, _rotate = 0, _width, _height;

  String _url;


  String get url => _url;

  int get cropW => _cropW;

  int get cropH => _cropH;

  int get cropX => _cropX;

  int get cropY => _cropY;

  int get rotate => _rotate;

  int get width => _width;

  int get height => _height;

  bool get mirrorVertical => _mirrorVertical;

  bool get mirrorHorizontal => _mirrorHorizontal;


  String toString() => {
      "url":url, "cropW": cropW, "cropH":cropH, "cropX":cropX, "cropY":cropY, "rotate":rotate, "width":width, "height":height, "mirrorVertical":mirrorVertical, "mirrorHorizontal": mirrorHorizontal
  }.toString();

  bool operator==(ImageEditProperties p) => p.url == url &&
                                            p.cropW == cropW &&
                                            p.cropH == cropH &&
                                            p.cropX == cropX &&
                                            p.cropY == cropY &&
                                            p.rotate == rotate &&
                                            p.width == width &&
                                            p.height == height &&
                                            p.mirrorHorizontal == mirrorHorizontal &&
                                            p.mirrorVertical == mirrorVertical;

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


  StreamController<bool> _loadStreamController = new StreamController<bool>();

  Stream<bool> _loadStream;

  bool _isReady = false;

  factory ImageEditor(ImageElement img) => _cache.putIfAbsent(img, () => new ImageEditor._internal(img));

  ImageEditor._internal(this.image){
    var properties = new ImageEditProperties.fromImageElement(image);
    if (properties.url == null) {
      return;
    }

    _handler = new CanvasHandler(canvas);
    _originalWidth = _handler.width = properties.width;
    _originalHeight = _handler.height = properties.height;
    _handler.addLayer(_imageLayer);
    _fullImage = new ImageElement(src:properties.url);
    _fullImage.onLoad.listen((_) {
      _image = new ImageCanvasShape(_fullImage, width:_originalWidth, height:_originalHeight);
      _imageLayer.addShape(_image);
      _handler.updateCanvas();
      _cropShape = new ImageCropCanvasShape(_image);
      _cropLayer.addShape(_cropShape);
      this.properties = properties;
      _isReady = true;
      _loadStreamController.add(true);
    });
  }


  Stream<bool> get onLoad => _loadStream == null ? _loadStream = _loadStreamController.stream.asBroadcastStream() : _loadStream;

  bool get isReady => _isReady;


  set properties(ImageEditProperties properties) {
    if (_handler == null) {
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

  ImageEditProperties get properties => new ImageEditProperties(_fullImage.src, mirrorHorizontal:this.mirrorHorizontal, mirrorVertical:this.mirrorVertical, cropW:hasCrop ? _cropShape.cropW : null, cropH:hasCrop ? _cropShape.cropH : null, cropX:hasCrop ? _cropShape.cropX : null, cropY:hasCrop ? _cropShape.cropY : null, rotate:this.rotate, width:_image.width.toInt(), height:_image.height.toInt());


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
    if (maxWidth != null && _handler.width > maxWidth) {
      width = maxWidth;
    }
    if (maxHeight != null && _handler.height > maxHeight) {
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
      _image.mirror = _cropShape.mirror = CanvasShape.MIRROR_BOTH;
      return;
    }

    if (vertical) {
      _image.mirror = _cropShape.mirror = CanvasShape.MIRROR_VERTICAL;
      return;
    }

    if (horizontal) {
      _image.mirror = _cropShape.mirror = CanvasShape.MIRROR_HORIZONTAL;
      return;
    }

    _image.mirror = _cropShape.mirror = CanvasShape.NO_MIRROR;
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


  int get cropX => _cropShape == null?null:_cropShape.cropX;

  int get cropY => _cropShape == null?null:_cropShape.cropY;

  int get cropW => _cropShape == null?null:_cropShape.cropW;

  int get cropH => _cropShape == null?null:_cropShape.cropH;

  num _interval(num n, num min, num max) => Math.max(min, Math.min(max, n));

  CanvasShape get dotNE => _cropShape == null?null:_cropShape.dotNE;

  CanvasShape get dotNW => _cropShape == null?null:_cropShape.dotNW;

  CanvasShape get dotSE => _cropShape == null?null:_cropShape.dotSE;

  CanvasShape get dotSW => _cropShape == null?null:_cropShape.dotSW;

/*  CanvasShape get dotNE => rotate == 0? _cropShape.dotNE:(rotate==1?_cropShape.dotNW:(rotate==2?_cropShape.dotSW:_cropShape.dotSE));
  CanvasShape get dotNW => rotate == 0? _cropShape.dotNW:(rotate==1?_cropShape.dotSW:(rotate==2?_cropShape.dotSE:_cropShape.dotNE));
  CanvasShape get dotSE => rotate == 0? _cropShape.dotSE:(rotate==1?_cropShape.dotNE:(rotate==2?_cropShape.dotNW:_cropShape.dotSW));
  CanvasShape get dotSW => rotate == 0? _cropShape.dotSW:(rotate==1?_cropShape.dotSE:(rotate==2?_cropShape.dotNE:_cropShape.dotNW));
*/


}
