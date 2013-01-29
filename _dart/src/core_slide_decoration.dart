part of core;

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