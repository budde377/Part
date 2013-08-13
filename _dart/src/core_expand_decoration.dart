part of core;

abstract class ExpandDecoration {
  final Element element;
  Animation expandAnimation, contractAnimation;
  StreamSubscription _mouseOver, _mouseOut;

  ExpandDecoration(Element element):element = element;

  void expand() {
    initialize();
    contractAnimation.stop();
    expandAnimation.start();
  }

  void contract() {
    initialize();
    expandAnimation.stop();
    contractAnimation.start();
  }

  void initialize(){}

  bool get expanded;

  bool get expandOnMouseOver => _mouseOver != null;

       set expandOnMouseOver(bool b){
         if(b == expandOnMouseOver){
           return;
         }
         if(b){
           _mouseOver = element.onMouseOver.listen((_)=>expand());
         } else {
           _mouseOver.cancel();
           _mouseOver = null;
         }
       }

  bool get contractOnMouseOut => _mouseOut != null;

  set contractOnMouseOut(bool b){
    if(b == contractOnMouseOut){
      return;
    }
    if(b){
      _mouseOut = element.onMouseOut.listen((_)=>contract());
    } else {
      _mouseOut.cancel();
      _mouseOut = null;
    }
  }

}


class BackgroundPositionExpandDecoration extends ExpandDecoration{
  final int startX, startY, endX, endY;

  BackgroundPositionExpandDecoration(Element element, { startX:null, startY:null, endX:null, endY:null, expandDuration:null, contractDuration:null}) : super(element), this.startX = startX, this.startY = startY, this.endY = endY, this.endX = endX{
    var backgroundSetter = _buildBackgroundSetter();
    expandAnimation = new Animation(expandDuration == null?new Duration(milliseconds:200):expandDuration,backgroundSetter);
    contractAnimation = new Animation(contractDuration== null?new Duration(milliseconds:200):contractDuration, (double pct)=>backgroundSetter(1-pct));
  }

  Function _buildBackgroundSetter(){
    var func = (_){};
    if(startX != null && endX != null){
      func = (double pct)=>element.style.backgroundPositionX = "${startX+(endX-startX)*pct}px";
      func = (double pct)=>print(pct);
    }
    if(startY != null && endY != null){
      var f = func;
      func = (double pct){
        f(pct);
        element.style.backgroundPositionY = "${startY+(endY-startY)*pct}px";
      };
    }
    return func;
  }

}


class OpacityExpandDecoration extends ExpandDecoration{
  final double startOpacity, endOpacity;
  Duration _dur = new Duration(seconds:1);

  OpacityExpandDecoration(Element element, this.startOpacity, this.endOpacity, {Duration expandDuration:null, Duration, contractDuration:null}) : super(element) {
    expandAnimation = new Animation(expandDuration==null?_dur:expandDuration, _setOpacity);
    contractAnimation = new Animation(contractDuration == null?_dur:contractDuration, (double pct)=>_setOpacity(1-pct));
  }

  void _setOpacity(double pct) => element.style.opacity = "${startOpacity+(endOpacity-startOpacity)*pct}";

}