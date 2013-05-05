part of core;

InfoBox addInfoBoxToElement(Element element,String infoHtml){
  var infoBox = new InfoBox(infoHtml);
  var p = element.offsetParent;
  var top = element.offsetTop, left = element.offsetLeft;
  while(p != null){
    top+= p.offsetTop;
    left+= p.offsetLeft;
    p = p.offsetParent;
  }
  infoBox.showAt(left+(element.clientWidth/2).toInt(), top);
  return infoBox;
}


class InfoBox{

  static const String COLOR_RED = "red";
  static const String COLOR_WHITE = "white";
  final DivElement element =  new DivElement();

  DivElement  _textElement, _arrowElement;

  InfoBox(infoHtml){
    _textElement = new DivElement();
    _arrowElement = new DivElement();
    _textElement.innerHtml = infoHtml;
    element.classes.add('infoBox');
    _textElement.classes.add('text');
    _arrowElement.classes.add('arrow');

    element.append(_textElement);
    element.append(_arrowElement);
  }

  String get infoHtml => _textElement.innerHtml;

  void set infoHtml(String html){
    _textElement.innerHtml = html;
  }


  String get backgroundColor => element.classes.contains('red')?COLOR_RED:COLOR_WHITE;

  void set backgroundColor(String color){
    switch(color){
      case COLOR_RED:
        element.classes.add(COLOR_RED);
      break;
      case COLOR_WHITE:
        element.classes.remove(COLOR_RED);
        break;
    }
  }

  void remove() =>element.remove();

  void showAt(int x, int y){
    query('body').append(element);
    x = x-(element.clientWidth/2).toInt();
    y = y-element.clientHeight-_arrowElement.clientHeight;
    element.style.top = "${y}px";
    element.style.left = "${x}px";
  }
}