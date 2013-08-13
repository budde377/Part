part of core;

abstract class FloatingBox{
  final DivElement element =  new DivElement();
  Function _removeFunction = (){};
  StreamSubscription _escSubscription, _mouseDownSubscription;
  bool _f = false;

  FloatingBox(){
    element.classes.add('floating_box');
  }

  bool get _inDom => element.parent != null;
  void _changeListener (void f()) => (_) {
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
          break;
        case "absolute":
          return int.parse(computedOffset.substring(0, computedOffset.length-2)) + recursiveCall(e.offsetParent);
          break;
        default:
          return e == null ? 0 : offset(e) + recursiveCall(e.offsetParent);
          break;
      }

    };
    var r = recursiveCall(e);
    _fixed = fixed;
    return r;

  }

  void showAboveCenterOfElement(Element e){
    var s = () => showAt( _elementOffsetLeft(e)+e.clientWidth~/2, _elementOffsetTop(e));
    s();
    _setUpChangeListener(_changeListener(s));

  }

  void showAboveRightOfElement(Element e){
    var s = () => showAt( _elementOffsetLeft(e)+e.clientWidth, _elementOffsetTop(e));
    s();
    _setUpChangeListener(_changeListener(s));

  }

  void showBelowCenterOfElement(Element e){
    var s = () => showAt( _elementOffsetLeft(e)+e.clientWidth~/2, _elementOffsetTop(e)+e.clientHeight);
    s();
    _setUpChangeListener(_changeListener(s));

  }

  void _setUpChangeListener(void l(Event e)){
    window.onResize.listen(l);
    window.onScroll.listen(l);
  }

  void showBelowLeftOfElement(Element e){
    var s = () => showAt(_elementOffsetLeft(e), _elementOffsetTop(e)+e.clientHeight);
    s();
    _setUpChangeListener(_changeListener(s));
  }


  bool get removeOnESC => _escSubscription != null;

  set removeOnESC(bool b){
    if(b == removeOnESC) {
      return;
    }
    if(b){
      _escSubscription = window.onKeyDown.listen((KeyboardEvent e){
        if(e.keyCode == 27){
          remove();
        }
      });
    } else {
      _escSubscription.cancel();
      _escSubscription = null;
    }
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


  void _addRemoveFunction(void f()){
    var rf = _removeFunction;
    _removeFunction = (){
      rf();
      f();
    };
  }

  void listenOnRemove(void f()) => _addRemoveFunction(f);

  void remove(){
    if(!_inDom){
      return;
    }
    _removeFunction();
    element.remove();
  }
  void showAt(int x, int y);
}


class InfoBox extends FloatingBox{

  static const String COLOR_RED = "red";
  static const String COLOR_WHITE = "white";
  static const String COLOR_BLACK = "black";
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


  String get backgroundColor => element.classes.contains('red')?COLOR_RED:(element.classes.contains('white')?COLOR_WHITE:COLOR_BLACK);

  void set backgroundColor(String color){
    element.classes.remove(COLOR_RED);
    element.classes.remove(COLOR_BLACK);
    switch(color){
      case COLOR_RED:
        element.classes.add(COLOR_RED);
      break;
      case COLOR_BLACK:
        element.classes.add(COLOR_BLACK);
    }
  }
  void showAt(int x, int y){
    query('body').append(element);
    x = x-(element.clientWidth/2).toInt();
    y = y-(reversed ? 0: element.clientHeight-_arrowElement.clientHeight);
    element.style.top = "${y}px";
    element.style.left = "${x}px";
  }

}

class DropDown{
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



  DropDown._internal(this.content){
    _dropBox = new DropDownBox(content);
    _arrow.classes.add('arrow_down');
    _text.classes.add('text');
    element..classes.add('drop_down')
    ..append(_arrow);
    _dropBox.listenOnRemove(()=>element.classes.remove('active'));
    closeOnESC = true;
    element.onClick.listen((_)=>toggle());
    document.onMouseDown.listen((MouseEvent e){
      if(element.contains(e.toElement) || _dropBox.element.contains(e.toElement)){
        return;
      }
      close();
    });
  }

  void toggle() => _dropBox.element.parent == null? open() : close();

  void open(){
    _dropBox.showBelowLeftOfElement(element);
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


  void showAt(int x, int y){
    query('body').append(element);
    element.style.top = "${y}px";
    element.style.left = "${x}px";
  }


}