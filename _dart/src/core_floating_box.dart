part of core;

InfoBox addInfoBoxToElement(Element element,String infoHtml, [String color = InfoBox.COLOR_WHITE]){
  var infoBox = new InfoBox(infoHtml);
  var p = element.offsetParent;
  var top = element.offsetTop, left = element.offsetLeft;
  while(p != null){
    top+= p.offsetTop;
    left+= p.offsetLeft;
    p = p.offsetParent;
  }
  infoBox.showAt(left+(element.clientWidth/2).toInt(), top);
  infoBox.backgroundColor = color;
  return infoBox;
}




abstract class FloatingBox{
  final DivElement element =  new DivElement();
  Function _removeFunction = (){};
  StreamSubscription _escSubscription, _mouseDownSubscription;

  FloatingBox(){
    element.classes.add('floating_box');
  }

  bool get _inDom => element.parent != null;

  void showAboveCenterOfElement(Element e){
    var p = e.offsetParent;
    var top = e.offsetTop, left = e.offsetLeft;
    while(p != null){
      top+= p.offsetTop;
      left+= p.offsetLeft;
      p = p.offsetParent;
    }
    showAt(left+(element.clientWidth/2).toInt(), top);
    window.onResize.listen((_){
      if(_inDom){
        showAboveCenterOfElement(e);
      }
    });
  }

  void showBelowLeftOfElement(Element e){
    var p = e.offsetParent;
    var top = e.offsetTop, left = e.offsetLeft;
    while(p != null){
      top+= p.offsetTop;
      left+= p.offsetLeft;
      p = p.offsetParent;
    }
    showAt(left, top+e.clientHeight);
    window.onResize.listen((_){
      if(_inDom){
        showBelowLeftOfElement(e);
      }
    });
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

  DivElement  _textElement, _arrowElement;

  InfoBox(infoHtml){
    _textElement = new DivElement();
    _arrowElement = new DivElement();
    _textElement.innerHtml = infoHtml;
    element.classes.add('info_box');
    _textElement.classes.add('text');
    _arrowElement.classes.add('arrow');

    element.append(_textElement);
    element.append(_arrowElement);
  }

  String get infoHtml => _textElement.innerHtml;

  void set infoHtml(String html){
    _textElement.innerHtml = html;
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
    y = y-element.clientHeight-_arrowElement.clientHeight;
    element.style.top = "${y}px";
    element.style.left = "${x}px";
  }

}

class DropDown{
  final DivElement content, element = new DivElement();
  DivElement _arrow = new DivElement(), _text = new DivElement();
  StreamSubscription _preventDefaultElement, _preventDefaultDropBox;
  DropDownBox _dropBox;

  DropDown(this.content){
    setUp();
  }

  DropDown.fromLIList(List<LIElement> lis) : content = new UListElement() {
    content.children = lis;
    setUp();
  }

  void setUp(){
    _dropBox = new DropDownBox(content);
    _arrow.classes.add('arrowDown');
    element..classes.add('drop_down')
    ..append(_text)
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

  set text(String t) => _text.text = t;

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