part of elements;

class ImageEditProperties {

  ImageEditProperties(String url, {bool mirrorVertical:false, bool mirrorHorizontal:false, int cropW:null, int cropH:null, int cropX:null, int cropY:null, int rotate:0, width:null, height:null}) {
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
        case 'S':
          _width = int.parse(vars[0]);
          _height = int.parse(vars[1]);
          break;
        case 'C':
          _cropX = int.parse(vars[0]);
          _cropY = int.parse(vars[1]);
          _cropW = int.parse(vars[2]);
          _cropH = int.parse(vars[3]);
          break;
        case 'R':
          _rotate = int.parse(vars[0]);
          break;
        case 'M':
          _mirrorVertical = int.parse(vars[0]) > 0;
          _mirrorHorizontal = int.parse(vars[1]) > 0;
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

  bool operator==(ImageEditProperties p) => (p.url.length < url.length? url.endsWith(p.url): p.url.endsWith(url)) &&
                                            p.cropW == cropW &&
                                            p.cropH == cropH &&
                                            p.cropX == cropX &&
                                            p.cropY == cropY &&
                                            p.rotate == rotate &&
                                            p.width == width &&
                                            p.height == height &&
                                            p.mirrorHorizontal == mirrorHorizontal &&
                                            p.mirrorVertical == mirrorVertical;

  int get hashCode => "${cropW}${cropH}${cropX}${cropY}${rotate}${width}${height}${mirrorHorizontal}${mirrorVertical}".hashCode;

  //TODO write better get hashcode
}


class ImageEditor {
  static final Map<ImageElement, ImageEditor> _cache = new Map<ImageElement, ImageEditor>();

  final CanvasElement canvas = new CanvasElement();

  final ImageElement image;

  ImageElement _fullImage;

  int _rotate = 0;

  int _originalHeight, _originalWidth;

  int maxWidth, minWidth, maxHeight, minHeight;

  int _cropW, _cropH;

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
    var rotated = properties.rotate % 2 == 1;
    _originalWidth = properties.width;
    _originalHeight = properties.height;
    _handler.width = rotated?_originalHeight:_originalWidth;
    _handler.height = rotated?_originalWidth:_originalHeight;
    _handler.addLayer(_imageLayer);
    _fullImage = new ImageElement(src:"/"+properties.url);
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
    print(properties);
    _handler.doWithoutUpdate(() {
      if (properties.rotate != null) {
        rotate = properties.rotate;
      }

      if (properties.width != null) {
        if(isRotated){
          _updateSize(properties.height, properties.width);
        } else {
          _updateSize(properties.width, properties.height);
        }
      }
      if (properties.cropX != null) {
        setCrop(properties.cropX, properties.cropY , properties.cropW , properties.cropH );
      } else {
        removeCrop();
      }
      mirrorHorizontal = properties.mirrorHorizontal;
      mirrorVertical = properties.mirrorVertical;
    });
    _handler.updateCanvas();
  }

  ImageEditProperties get properties => new ImageEditProperties(_fullImage.src,
                                                                mirrorHorizontal:this.mirrorHorizontal,
                                                                mirrorVertical:this.mirrorVertical,
                                                                cropW:hasCrop ? _cropShape.cropW.floor() : null,
                                                                cropH:hasCrop ? _cropShape.cropH.floor() : null,
                                                                cropX:hasCrop ? _cropShape.cropX.floor() : null,
                                                                cropY:hasCrop ? _cropShape.cropY.floor() : null,
                                                                rotate:this.rotate,
                                                                width:_image.width.toInt(),
                                                                height:_image.height.toInt());


  int get rotate => _rotate;

  bool get isRotated => _rotate % 2 == 1;

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

    var w = (isRotated ? _originalHeight / _originalWidth : ratio ) * h;
    _updateSize(w.toInt(), h);
  }

  int get width => _handler.width;

  set width(int w) {
    var h = (isRotated ? ratio : _originalHeight / _originalWidth) * w;
    _updateSize(w, h.toInt());
  }


  void setCrop(int x, int y, int width, int height) {
    _cropShape.setCrop(x, y, width, height);
    _cropW = width;
    _cropH = height;
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
      var ratioW = w/_handler.width, ratioH = h/_handler.height;
      if(hasCrop){
        var cw = _cropShape.cropW*ratioW, ch = _cropShape.cropH*ratioH;
        if(cw < 10 || ch < 10){
          return;
        }
        _cropShape.setCrop(_cropShape.cropX*ratioW, _cropShape.cropY*ratioH, cw, ch);
      }
      _image.width = isRotated ? h : w;
      _image.height = isRotated ? w : h;
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

}



class ImageEditorHandler {
  static final Map<ImageEditor, ImageEditorHandler> _cache = new Map<ImageEditor, ImageEditorHandler>();

  ButtonElement _rcw = new ButtonElement(), _rccw = new ButtonElement(), _mirror_v = new ButtonElement(), _mirror_h = new ButtonElement(), _zoom_in = new ButtonElement(), _zoom_out = new ButtonElement(), _crop = new ButtonElement(), _save = new ButtonElement();

  DivElement _toolBar = new DivElement(), _dialogElement = new DivElement(), _savingBar = new DivElement();

  DialogBox _dialogBox;

  final ImageEditor editor;

  bool _saving = false;

  StreamController<ImageEditProperties> _editStreamController = new StreamController<ImageEditProperties>();
  Stream<ImageEditProperties> _stream;

  factory ImageEditorHandler(ImageEditor ie) => _cache.putIfAbsent(ie, () => new ImageEditorHandler._internal(ie));

  factory ImageEditorHandler.fromImage(ImageElement elm) => new ImageEditorHandler(new ImageEditor(elm));

  ImageEditProperties _properties;

  ImageEditorHandler._internal(this.editor) {
    var wrapper = new DivElement();
    _savingBar.classes.add("saving_bar");
    _dialogElement.append(editor.canvas);
    _dialogElement.append(_savingBar);
    _toolBar.classes.add('image_edit_tool_bar');
    _toolBar.append(wrapper);
    _dialogElement.classes.add('edit_image_popup');
    _rcw.classes.add('rotate_cw');
    _rccw.classes.add('rotate_ccw');
    _mirror_v.classes.add('mirror_v');
    _mirror_h.classes.add('mirror_h');
    _zoom_in.classes.add('zoom_in');
    _zoom_out.classes.add('zoom_out');
    _crop.classes.add('crop');
    _save.classes.add('save');

    wrapper..append(_rcw)..append(_rccw)..append(_mirror_v)..append(_mirror_h)..append(_zoom_in)..append(_zoom_out)..append(_crop)..append(_save);
    _setUpListeners();
  }


  Stream<ImageEditProperties> get onEdit => _stream == null?_stream = _editStreamController.stream.asBroadcastStream():_stream;

  int _inDot (num x, num y) {
    var p = transformMirror(x, y);
    x = p.x;
    y = p.y;

    return editor.dotNW.inShape(x, y) ? 1 : (editor.dotNE.inShape(x, y) ? 2 : (editor.dotSE.inShape(x, y) ? 3 : (editor.dotSW.inShape(x, y) ? 4 : 0)));
  }

  void _setUpListeners() {
    editor.canvas.onMouseWheel.listen((WheelEvent we) {
      editor.width += (we.deltaY > 0 ? 1 : -1) * 2;
      we.preventDefault();
    });
    var t;
    _zoom_in.onMouseDown.listen((_) {
      t = new Timer.periodic(new Duration(milliseconds:1), (_) {
        editor.width++;
      });
      var sub1;
      sub1 = document.onMouseUp.listen((_) {
        sub1.cancel();
        t.cancel();
      });
    });

    _zoom_out.onMouseDown.listen((_) {
      t = new Timer.periodic(new Duration(milliseconds:1), (_) {
        editor.width--;
      });
      var sub1;
      sub1 = document.onMouseUp.listen((_) {
        sub1.cancel();
        t.cancel();
      });

    });

    _save.onClick.listen((_) {
      var p = editor.properties;

      if(_properties == null || p == _properties || _saving){
        return;
      }
      var pb = new ProgressBar();
      pb.percentage = 0;
      _savingBar.append(pb.bar);
      _savingBar.classes.add("saving");
      _saving = true;
      ajaxClient.callFunction(new ImagePropertiesEditImageContentJSONFunction(p)).then((JSONResponse response){
        if(response.type != JSONResponse.RESPONSE_TYPE_SUCCESS){
          return;
        }
        pb.percentage = 1;
        var t = new Timer(new Duration(milliseconds:500), (){
          _savingBar.classes.remove("saving");
          pb.bar.remove();
        });
        editor.image.src = response.payload;
        _properties = p;
        _saving = false;
        _editStreamController.add(p);
      });

    });

    _rcw.onClick.listen((_) => editor.rotate++);
    _rccw.onClick.listen((_) => editor.rotate--);
    _mirror_v.onClick.listen((_) => editor.mirrorVertical = !editor.mirrorVertical);
    _mirror_h.onClick.listen((_) => editor.mirrorHorizontal = !editor.mirrorHorizontal);
    _crop.onClick.listen((_){
      if(editor.hasCrop){
        editor.removeCrop();
      } else {
        var w = editor.isRotated?editor.height:editor.width,
            h = editor.isRotated?editor.width:editor.height;
        editor.setCrop((0.25*w).floor(), (0.25*h).floor(), (0.5*w).floor(), (0.5*h).floor());
      }
    });




    editor.canvas.onMouseMove.listen((MouseEvent ev) {
      if(editor == null || !editor.hasCrop){
        return;
      }
      if (_inDot(ev.offset.x, ev.offset.y) > 0) {
        editor.canvas.classes.add("hover_dot");
      } else {
        editor.canvas.classes.remove("hover_dot");
      }
    });
    editor.canvas.onMouseDown.listen((MouseEvent ev) {
      var dotN;
      if ((dotN = _inDot(ev.offset.x, ev.offset.y)) == 0) {
        return ;
      }
      var sub1, sub2;
      sub1 = document.onMouseMove.listen((MouseEvent ev) {
        var mx = ev.movement.x, my = ev.movement.y;
        if(editor.mirrorHorizontal){
          my = -my;
        }

        if(editor.mirrorVertical){
          mx = -mx;
        }

        var p = rotatePoint(mx,my,0, 0, editor.rotate*Math.PI/2);
        mx = p.x;
        my = p.y;


        switch (dotN) {
          case 1:
            editor.setCrop(editor.cropX + mx, editor.cropY + my, -mx + editor.cropW, -my + editor.cropH);
            break;
          case 2:
            editor.setCrop(editor.cropX, editor.cropY + my, mx + editor.cropW, -my + editor.cropH);
            break;
          case 3:
            editor.setCrop(editor.cropX , editor.cropY , mx + editor.cropW, my + editor.cropH);
            break;
          case 4:
            editor.setCrop(editor.cropX + mx, editor.cropY , -mx + editor.cropW, my + editor.cropH);
            break;

        }

      });

      sub2 = document.onMouseUp.listen((MouseEvent ev) {
        sub1.cancel();
        sub2.cancel();
      });

    });
  }

  Position transformMirror(int x, int y) {


    if(editor.mirrorHorizontal){
      y = editor.height-y;
    }

    if(editor.mirrorVertical){
      x = editor.width-x;
    }

    return new Position(x:x, y:y);
  }

  void open() {
    _dialogBox = dialogContainer.dialog(_dialogElement);
    _dialogBox.onClose.listen((_) => close());
    body.append(_toolBar);

    if(editor.isReady){
      _properties = editor.properties = new ImageEditProperties.fromImageElement(editor.image);
    } else {
      editor.onLoad.listen((_){
        _properties = editor.properties = new ImageEditProperties.fromImageElement(editor.image);
      });
    }


  }

  void close() {
    if (_dialogBox == null) {
      return;
    }
    _toolBar.remove();

    _dialogBox = null;
  }


}