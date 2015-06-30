part of elements;

abstract class ExpandDecoration {
  final Element element;
  core.Animation expandAnimation, contractAnimation;
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


abstract class SlideDecoration{
  final int numIndexes;
  final Element element;
  SlideDecoration(int numIndexes, Element element):this.numIndexes = numIndexes,this.element = element;

  void next(){
    if(currentIndex < numIndexes-1){
      goToIndex(currentIndex+1);
    }
  }
  void prev(){
    if(currentIndex > 0){
      goToIndex(currentIndex-1);
    }
  }

  void goToIndex(int index);

  int get currentIndex;
}