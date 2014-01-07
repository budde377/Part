part of elements;

Position rotatePoint(num x, num y, num ox, num oy, num r) =>  new Position(x:(x-ox)*Math.cos(r) + (y-oy)*Math.sin(r)+ox,
y:-(x-ox)*Math.sin(r) + (y-oy)*Math.cos(r)+oy);


class CanvasHandler {

  static Map<CanvasElement, CanvasHandler> _cache = new Map<CanvasElement, CanvasHandler>();

  final CanvasElement canvas;

  CanvasRenderingContext2D _context;

  List<CanvasLayer> _layers = new List<CanvasLayer>();

  bool _enableUpdate = true;

  factory CanvasHandler (CanvasElement canvas) => _cache.putIfAbsent(canvas, () => new CanvasHandler._internal(canvas));

  CanvasHandler._internal(this.canvas){
    _context = canvas.context2D;

  }

  void doWithoutUpdate(action()) {
    _enableUpdate = false;
    action();
    _enableUpdate = true;
  }

  void updateCanvas() {
    if (!_enableUpdate) {
      return;
    }
    var m = Math.max(height, width);
    _context.clearRect(-m, -m, m * 2, m * 2);
    _layers.forEach((CanvasLayer l) => l.draw());
  }

  void drawShape(CanvasShape shape) {
    doWithoutUpdate(() => shape.beforeDraw(this));
    var draw = () => shape.draw(_context);
    var b1 = shape.rotation != 0, b2 = shape.mirror;
    if (b1 || b2 > 0) {
      _context.save();
      _context.translate(shape.rotateX, shape.rotateY);
      if (b2 > 0) {
        _context.scale(b2 == CanvasShape.MIRROR_VERTICAL || b2 == CanvasShape.MIRROR_BOTH ? -1 : 1, b2 == CanvasShape.MIRROR_HORIZONTAL || b2 == CanvasShape.MIRROR_BOTH ? -1 : 1);
      }

      if (b1) {
        _context.rotate(shape.rotation);
      }
      _context.translate(-shape.rotateX, -shape.rotateY);
      draw();
      _context.restore();
      return;
    }

    draw();
  }

  CanvasLayer addNewLayer() {
    var l = new CanvasLayer();
    l.handler = this;
    addLayer(l);
    return l;
  }

  void removeLayer(CanvasLayer layer) {
    _layers.removeWhere((CanvasLayer l) => l == layer);
    updateCanvas();
  }

  void addLayer(CanvasLayer layer) {
    layer.handler = this;
    _layers.add(layer);
    updateCanvas();
  }

  set width(int w) {
    canvas.width = w;
    updateCanvas();
  }

  get width => canvas.width;

  set height(int h) {
    canvas.height = h;
    updateCanvas();
  }

  get height => canvas.height;

  CanvasShape shapeAt(num x, num y) {
    var s;
    _layers.reversed.any((CanvasLayer l) => (s = l.shapeAt(x, y)) != null);
    return s;
  }

}


class CanvasLayer {
  CanvasHandler _handler;

  List<CanvasShape> _shapes = new List<CanvasShape>();

  set handler(CanvasHandler h) => _handler = h;

  bool get handlerSet => _handler != null;

  void updateLayer() {
    if (!handlerSet) {
      return;
    }
    _handler.updateCanvas();
  }

  void draw() {
    if (!handlerSet) {
      return;
    }
    _shapes.forEach((CanvasShape sh) => _handler.drawShape(sh));
  }


  void doWithoutUpdate(action()) {
    if (!handlerSet) {
      return;
    }
    _handler.doWithoutUpdate(action);
  }

  void addShape(CanvasShape shape) {
    _shapes.add(shape);
    shape.layer = this;
    if (!handlerSet) {
      return;
    }
    _handler.updateCanvas();
  }

  void removeShape(CanvasShape shape) {
    _shapes.removeWhere((CanvasShape s) => s == shape);
    shape.layer = null;
    if (!handlerSet) {
      return;
    }
    _handler.updateCanvas();
  }

  void remove() {
    if (!handlerSet) {
      return;
    }
    _handler.removeLayer(this);
    _handler = null;
  }

  CanvasShape shapeAt(num x, num y) => _shapes.reversed.firstWhere((CanvasShape sh) => sh.inShape(x, y), orElse:() => null);


}

abstract class CanvasShape {

  static const NO_MIRROR = 0;

  static const MIRROR_VERTICAL = 1;

  static const MIRROR_HORIZONTAL = 2;

  static const MIRROR_BOTH = 3;

  num _x = 0, _y = 0, _mirror = 0, _rotate = 0, _rotateX = 0, _rotateY = 0;

  CanvasLayer _layer;

  num get x => _x;

  set x(num x) => doUpdate(() => _x = x);

  void doUpdate([action()]) {
    if (action != null) {
      action();
    }
    if (_layer == null) {
      return;
    }
    _layer.updateLayer();
  }

  void doWithoutUpdate(action()) {
    if (!layerSet) {
      return;
    }
    _layer.doWithoutUpdate(action);
  }

  num get y => _y;

  set y(num y) => doUpdate(() => _y = y);

  num get rotateX => _rotateX;

  set rotateX(num x) => doUpdate(() => _rotateX = x);

  num get rotateY => _rotateY;

  set rotateY(num y) => doUpdate(() => _rotateY = y);


  num get rotation => _rotate;

  set rotation(num r) => doUpdate(() => _rotate = r);

  set rotationDeg(num d) => rotation = d * Math.PI / 180;

  bool get layerSet => _layer != null;

  int get mirror => _mirror;

  set mirror(int m) => doUpdate(() => _mirror = m % 4);

  void erase() {
    if (_layer == null) {
      return;
    }
    _layer.removeShape(this);
  }

  CanvasLayer get layer => _layer;

  set layer(CanvasLayer l) => doUpdate(() => _layer = l);


  void draw(CanvasRenderingContext2D context);

  bool inShape(num x, num y);

  void remove() {
    if (layerSet) {
      _layer.removeShape(this);
    }
  }

  void beforeDraw(CanvasHandler h) {
  }

  Position rotatePosition(num x, num y,  {num r, num rx, num ry}){


    r = r == null? _rotate: r;
    rx = rx == null? _rotateX : rx;
    ry = ry == null? _rotateY : ry;

    return rotatePoint(x,y,rx,ry,r);
  }

}

abstract class StrokeFillCanvasShape extends CanvasShape {
  String _fillStyle = "#000";

  String _strokeStyle = "#000";

  num _strokeWidth = 0;

  String get fillStyle => _fillStyle;

  set fillStyle(String s) => doUpdate(() => _fillStyle = s);

  String get strokeStyle => _strokeStyle;

  set strokeStyle(String s) => doUpdate(() => _strokeStyle = s);


  num get strokeWidth => _strokeWidth;

  set strokeWidth(num s) => doUpdate(() => _strokeWidth = s);

  draw(CanvasRenderingContext2D context) {
    if (_fillStyle != null) {
      context.fillStyle = _fillStyle;
    }
    context.lineWidth = _strokeWidth;
    if (_strokeWidth != 0 && _strokeStyle != null) {
      context.strokeStyle = _strokeStyle;
    }
  }


}


class CircleCanvasShape extends StrokeFillCanvasShape {
  num _radius;

  CircleCanvasShape([this._radius = 15]);

  num get radius => _radius;

  set radius(num n) {
    _radius = n;
    if (layer == null) {
      return;
    }
    layer.updateLayer();
  }

  void draw(CanvasRenderingContext2D context) {
    super.draw(context);
    context.beginPath();
    context.arc(x, y, _radius, 0, Math.PI * 2);
    context.closePath();
    if (fillStyle != null) {
      context.fill();
    }
    if (strokeStyle != null && strokeWidth != 0) {
      context.stroke();
    }
  }

  bool inShape(num x, num y){
    var p = rotatePosition(x,y);
    return Math.sqrt(Math.pow(p.x - this.x, 2) + Math.pow(p.y - this.y, 2)) <= _radius;
  }
}


class RectCanvasShape extends StrokeFillCanvasShape {


  num _width, _height;

  RectCanvasShape([this._width = 100, this._height = 100]);

  num get width => _width;

  set width(num n) {
    _width = n;
    if (layer == null) {
      return;
    }
    layer.updateLayer();
  }

  num get height => _height;

  set height(num n) {
    _height = n;
    if (layer == null) {
      return;
    }
    layer.updateLayer();
  }

  void draw(CanvasRenderingContext2D context) {
    super.draw(context);
    context.fillRect(x, y, _width, _height);
  }

  bool inShape(num x, num y){
    var p = rotatePosition(x,y);
    return p.x >= this.x && p.y >= this.y && p.x <= this.x + width && p.y <= this.y + height;
  }
}


class ImageCanvasShape extends StrokeFillCanvasShape {

  final ImageElement image;


  num _width, _height;

  ImageCanvasShape(this.image,{int width, int height}) {
    _width = width == null ? image.clientWidth:width;
    _height = height == null ? image.clientHeight: height;

  }

  void draw(CanvasRenderingContext2D context) {
    context.drawImageScaled(image, x, y, _width, _height);
  }

  void beforeDraw(CanvasHandler h) {
    rotateX = h.width / 2;
    rotateY = h.height / 2;
    y = (h.height - height) / 2;
    x = (h.width - width) / 2;
  }

  num get width => _width;

  num get height => _height;

  set width(num w) => _width = w;

  set height(num h) => _height = h;



  bool inShape(num x, num y){

    var p = rotatePosition(x,y);
    return p.x >= this.x && p.y >= this.y && p.x <= this.x + width && p.y <= this.y + height;
  }

}


class ImageCropCanvasShape extends StrokeFillCanvasShape {

  final ImageCanvasShape image;

  RectCanvasShape _rect1, _rect2, _rect3, _rect4;

  CircleCanvasShape _dot1, _dot2, _dot3, _dot4;

  int _width, _height, _cropX, _cropY, _cropW, _cropH;

  num _cx, _cy, _cw, _ch;


  num get cropX => _cropX;
  num get cropY => _cropY;
  num get cropW => _cropW;
  num get cropH => _cropH;


  ImageCropCanvasShape(this.image) {
    _width = image.width;
    _height = image.height;
    _rect1 = new RectCanvasShape();
    _rect2 = new RectCanvasShape();
    _rect3 = new RectCanvasShape();
    _rect4 = new RectCanvasShape();
    _rect1.fillStyle = _rect2.fillStyle = _rect3.fillStyle = _rect4.fillStyle = "rgba(255,255,255,0.5)";
    _dot1 = new CircleCanvasShape(5);
    _dot2 = new CircleCanvasShape(5);
    _dot3 = new CircleCanvasShape(5);
    _dot4 = new CircleCanvasShape(5);
    _dot1.fillStyle = _dot2.fillStyle = _dot3.fillStyle = _dot4.fillStyle = "rgba(0,0,0,0.7)";
  }

  void setCrop(num x, num y, num width, num height) {
    _cw = width;
    _ch = height;
    _cy = y;
    _cx = x;
    if(_layer == null){
      _updateCrop();
    } else {
      _layer.doWithoutUpdate(_updateCrop);
      _layer.updateLayer();
    }
  }

  void _updateCrop() {

    var _cw = Math.min(this._cw, _width-_cx),
    _ch = Math.min(this._ch, _height-_cy);



    if(_cx < 0 || _cy <0 || _cw < 10 || _ch < 10 || _cx >= _width-10 || _cy>= _height-10 ){
      return;
    }

    _cropX = _cx;
    _cropY = _cy;
    _cropW = _cw;
    _cropH = _ch;

    _rect1.x = x;
    _rect1.y = y;
    _rect1.width = _cropX;
    _rect1.height = _height;
    _rect2.x = x + _cropX;
    _rect2.y = y + 0;
    _rect2.width = _cropW;
    _rect2.height = _cropY;
    _rect3.x = x + _cropX + _cropW;
    _rect3.y = y;
    _rect3.width = _width - (_cropW + _cropX);
    _rect3.height = _height;
    _rect4.x = x + _cropX;
    _rect4.y = y + _cropY + _cropH;
    _rect4.width = _cropW;
    _rect4.height = _height - _cropY - _cropH;
    _dot1.x = x + _cropX;
    _dot1.y = y + _cropY;
    _dot2.x = x + _cropX + _cropW;
    _dot2.y = y + _cropY;
    _dot3.x = x + _cropX;
    _dot3.y = y + _cropY + _cropH;
    _dot4.x = x + _cropX + _cropW;
    _dot4.y = y + _cropY + _cropH;
  }

  set layer(CanvasLayer l){
      l..addShape(_rect1)
       ..addShape(_rect2)
       ..addShape(_rect3)
       ..addShape(_rect4)
       ..addShape(_dot1)
       ..addShape(_dot2)
       ..addShape(_dot3)
       ..addShape(_dot4);
    super.layer = l;
  }


  void beforeDraw(CanvasHandler h) {
    _height = image.height;
    _width = image.width;
    rotateX = h.width / 2;
    rotateY = h.height / 2;
    y = (h.height - _height) / 2;
    x = (h.width - _width) / 2;
    _rect1.rotation = _rect2.rotation = _rect3.rotation = _rect4.rotation =
    _dot1.rotation = _dot2.rotation = _dot3.rotation = _dot4.rotation = rotation;
    _rect1.rotateX = _rect2.rotateX = _rect3.rotateX = _rect4.rotateX =
    _dot1.rotateX = _dot2.rotateX = _dot3.rotateX = _dot4.rotateX = rotateX;
    _rect1.rotateY = _rect2.rotateY = _rect3.rotateY = _rect4.rotateY =
    _dot1.rotateY = _dot2.rotateY = _dot3.rotateY = _dot4.rotateY = rotateY;
    _rect1.mirror = _rect2.mirror = _rect3.mirror = _rect4.mirror =
    _dot1.mirror = _dot2.mirror = _dot3.mirror = _dot4.mirror = mirror;
    _updateCrop();
  }

  void draw(CanvasRenderingContext2D context) {

  }

  CanvasShape get dotNW => _dot1;

  CanvasShape get dotNE => _dot2;

  CanvasShape get dotSW => _dot3;

  CanvasShape get dotSE => _dot4;

  bool inShape(num x, num y) => _rect1.inShape(x, y) || _rect2.inShape(x, y) || _rect3.inShape(x, y) || _rect4.inShape(x, y) || _dot1.inShape(x, y) || _dot2.inShape(x, y) || _dot3.inShape(x, y) || _dot4.inShape(x, y);

}