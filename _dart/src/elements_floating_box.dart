part of elements;

abstract class FloatingBox{
  final DivElement element =  new DivElement();
  StreamSubscription _mouseDownSubscription;
  StreamController<Event> _removeController = new StreamController<Event>();
  bool _f = false, _removeOnESC;

  FloatingBox(){
    element.classes.add('floating_box');
  }

  bool get _inDom => element.parent != null;
  Function _changeListener (void f()) => (_) {
    if(_inDom){
      f();
    }
  };

  bool get _fixed => _f;

  set _fixed (bool b){
    if(b == _fixed){
      return;
    }
    if(b){
      element.style.position = "fixed";
    } else {
      element.style.removeProperty("position");
    }
    _f = b;
  }


  int _elementOffsetTop(Element e) => _elementOffset(e, (Element e)=>e.getComputedStyle().top, () => window.scrollY, (Element e) => e.offsetTop);
  int _elementOffsetLeft(Element e) => _elementOffset(e, (Element e)=>e.getComputedStyle().left, () => window.scrollX, (Element e) => e.offsetLeft);

  int  _elementOffset(Element e, String computedStyleOffset(Element e), int scroll(), int offset(Element e)){
    var fixed = _fixed;
    var recursiveCall;
    recursiveCall = (Element e){
      if(e == null){
        return 0;
      }
      var computedOffset = computedStyleOffset(e).trim();
      var p = e.getComputedStyle().position;
      fixed = p == "fixed";
      switch( computedOffset == "auto"?"static":p){
        case "fixed":
          var i = int.parse(computedOffset.substring(0, computedOffset.length-2));
          return i;
        case "absolute":
          return int.parse(computedOffset.substring(0, computedOffset.length-2)) + recursiveCall(e.offsetParent);
        default:
          return e == null ? 0 : offset(e) + recursiveCall(e.offsetParent);

      }

    };
    var r = recursiveCall(e);
    _fixed = fixed;
    return r;

  }

  void showAboveCenterOfElement(Element e){
    var s = () => showAt( _elementOffsetLeft(e)+e.offsetWidth~/2, _elementOffsetTop(e));
    s();
    _setUpChangeListener(_changeListener(s));

  }

  void showAboveRightOfElement(Element e){
    var s = () => showAt( _elementOffsetLeft(e)+e.offsetWidth, _elementOffsetTop(e));
    s();
    _setUpChangeListener(_changeListener(s));

  }

  void showBelowCenterOfElement(Element e){
    var s = () => showAt( _elementOffsetLeft(e)+e.offsetWidth~/2, _elementOffsetTop(e)+e.offsetHeight);
    s();
    _setUpChangeListener(_changeListener(s));

  }

  void _setUpChangeListener(void l(Event e)){
    window.onResize.listen(l);
    window.onScroll.listen(l);
  }

  void showBelowLeftOfElement(Element e){
    var s = () => showAt(_elementOffsetLeft(e), _elementOffsetTop(e)+e.offsetHeight);
    s();
    _setUpChangeListener(_changeListener(s));
  }
  void showBelowRightOfElement(Element e){
    var s = () => showAt(_elementOffsetLeft(e)+e.offsetWidth, _elementOffsetTop(e)+e.offsetHeight);
    s();
    _setUpChangeListener(_changeListener(s));
  }
 void showBelowAlignRightOfElement(Element e){
    var s = () => showAt(_elementOffsetLeft(e)+e.offsetWidth-element.offsetWidth, _elementOffsetTop(e)+e.offsetHeight);
    s();
    s();
    _setUpChangeListener(_changeListener(s));
  }


  bool get removeOnESC => _removeOnESC == true;

  set removeOnESC(bool b){

    if(removeOnESC == b) {
      return;
    }
    if(_removeOnESC != null){
      _removeOnESC = b;
      return;
    }
    _removeOnESC = b;

  }

  bool _escRemover(){
    if(element.parent == null){
      return false;
    }
    if(_removeOnESC){
      remove();
      return true;
    }
    return false;
  }

  bool get removeOnMouseDownOutsideOfBox => _mouseDownSubscription != null;

  set removeOnMouseDownOutsideOfBox(bool b){
    if(b == removeOnMouseDownOutsideOfBox) {
      return;
    }
    if(b){
      _mouseDownSubscription = window.onMouseDown.listen((MouseEvent e){
        if(element.contains(e.toElement)){
          return;
        }
        remove();
      });
    } else {
      _mouseDownSubscription.cancel();
      _mouseDownSubscription = null;
    }
  }



  Stream<Event> get onRemove => _removeController.stream;


  void remove(){
    if(!_inDom){
      return;
    }
    _removeController.add(new Event("remove", canBubble:false, cancelable:false));
    element.remove();
  }
  void showAt(int x, int y){
    if(element.parent == null){
      query('body').append(element);
    }
    if(removeOnESC){
      escQueue.add(_escRemover);
    }
    element.style.top = "${y}px";
    element.style.left = "${x}px";
  }}


class InfoBox extends FloatingBox{

  static const String COLOR_RED = "red";
  static const String COLOR_WHITE = "white";
  static const String COLOR_BLACK = "black";
  static const String COLOR_GREYSCALE = "greyscale";
  final Element  content;
  DivElement _arrowElement = new DivElement();

  bool _reversed = false;

  InfoBox.elementContent(this.content){
    _setUp();
  }

  InfoBox(String infoHtml) : content = new DivElement(){
    content.innerHtml = infoHtml;
    _setUp();
  }

  void _setUp(){
    element.classes.add('info_box');
    content.classes.add('text');
    _arrowElement.classes.add('arrow');

    element.append(content);
    element.append(_arrowElement);
  }

  String get infoHtml => content.innerHtml;

  void set infoHtml(String html){
    content.innerHtml = html;
  }

  bool get reversed => _reversed;

       set reversed(bool b){
         if(b == _reversed){
           return;
         }
         element.classes.toggle('reversed');
         element.append(_reversed?_arrowElement:content);
         _reversed = b;
       }


  String get backgroundColor => element.classes.contains('red')?COLOR_RED:(element.classes.contains('white')?COLOR_WHITE:(element.classes.contains(COLOR_GREYSCALE)?COLOR_GREYSCALE:COLOR_BLACK));

  void set backgroundColor(String color){
    element.classes.remove(COLOR_RED);
    element.classes.remove(COLOR_BLACK);
    switch(color){
      case COLOR_RED:
        element.classes.add(COLOR_RED);
      break;
      case COLOR_BLACK:
        element.classes.add(COLOR_BLACK);
        break;
      case COLOR_GREYSCALE:
        element.classes.add(COLOR_GREYSCALE);
    }
  }
  void showAt(int x, int y){
    query('body').append(element);
    x = x-(element.clientWidth/2).toInt();
    y = y-(reversed ? 0: element.clientHeight-_arrowElement.clientHeight);
    super.showAt(x,y);
  }

}

class DropDown{
  static const int SHOW_BELOW_LEFT_OF_ELEMENT = 1,
            SHOW_BELOW_CENTER_OF_ELEMENT = 2,
            SHOW_BELOW_ALIGN_RIGHT = 3;

  int _showMode = SHOW_BELOW_LEFT_OF_ELEMENT;
  final Element content;
  final DivElement element = new DivElement();
  DivElement _arrow = new DivElement(), _text = new DivElement();
  StreamSubscription _preventDefaultElement, _preventDefaultDropBox;
  DropDownBox _dropBox;
  static final Map<Element, DropDown> _cache = new Map<Element, DropDown>();

  factory DropDown(element) => _cache.putIfAbsent(element, ()=>new DropDown._internal(element));

  factory DropDown.fromLIList(List<LIElement> lis){
    var ul = new UListElement();
    ul.children = lis;
    return _cache[ul] = new DropDown._internal(ul);
  }


  set showMode(int i) => _showMode = Math.min(3, Math.max(1, i));

  int get showMode => _showMode;


  DropDown._internal(this.content){
    _dropBox = new DropDownBox(content);
    _arrow.classes.add('arrow_down');
    _text.classes.add('text');
    element..classes.add('drop_down')
    ..append(_arrow);
    _dropBox.onRemove.listen((_)=>element.classes.remove('active'));
    closeOnESC = true;
    element.onClick.listen((_)=>toggle());
    document.onMouseDown.listen((MouseEvent e){
      if(element.contains(e.toElement) || _dropBox.element.contains(e.toElement)){
        return;
      }
      close();
    });
  }

  void toggle() {
    if(_dropBox.element.parent == null){
      open();
    } else {
      close();
    }
  }
  void open(){
    switch(_showMode){
      case SHOW_BELOW_LEFT_OF_ELEMENT:
        _dropBox.showBelowLeftOfElement(element);
    break;
    case SHOW_BELOW_CENTER_OF_ELEMENT:
    _dropBox.showBelowCenterOfElement(element);
    break;
    case SHOW_BELOW_ALIGN_RIGHT:
    _dropBox.showBelowAlignRightOfElement(element);
    break;

    }
    element.classes.add('active');
  }

  void close(){
    _dropBox.remove();
  }

  set text(String t){
    if(t.length > 0){
      _text.text = t;
      element.children.insert(0, _text);
    } else {
      _text.remove();
    }

  }
  String get text => _text.parent == null? "":_text.text;

  bool get preventDefaultOnClick => _preventDefaultDropBox != null;

  set preventDefaultOnClick (bool b){
    if(b == preventDefaultOnClick){
      return;
    }
    if(b){
      _preventDefaultDropBox = _dropBox.element.onMouseDown.listen((Event e) => e.preventDefault());
      _preventDefaultElement = element.onMouseDown.listen((Event e) => e.preventDefault());
    } else {
      _preventDefaultDropBox = _preventDefaultElement = null;
    }
  }

  bool get closeOnESC => _dropBox.removeOnESC;
  set closeOnESC (bool b) => _dropBox.removeOnESC = b;

  bool get closeOnMouseOutside => _dropBox.removeOnMouseDownOutsideOfBox;
  set closeOnMouseOutside (bool b) => _dropBox.removeOnMouseDownOutsideOfBox = b;

  DropDownBox get dropDownBox => _dropBox;

}

class DropDownBox extends FloatingBox{

  final Element _content;

  DropDownBox(this._content){
    element.classes.add("drop_down_box");
    element.append(_content);
  }

  void _changeActive(LIElement newActive, List<LIElement> siblings){
    siblings.forEach((LIElement e) => e.classes.remove('active'));
    if(newActive == null){
      return;
    }
    newActive.classes.add('active');
  }


  void showAt(int x, int y) => super.showAt(x, y);


}