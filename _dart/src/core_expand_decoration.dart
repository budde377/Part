part of core;

abstract class ExpandDecoration {
  final Element element;
  Animation expandAnimation, contractAnimation;

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

  void initialize();

  bool get expanded;

}