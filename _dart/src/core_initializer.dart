part of core;

abstract class Initializer{

  bool get canBeSetUp;
  void setUp();

}


class InitializerLibrary{
  final List<Initializer> _initializer = new List<Initializer>();

  void registerInitializer(Initializer initializer) => _initializer.add(initializer);

  void setUp(){
    while(_initializer.length > 0){
      var initializer = _initializer.removeAt(0);
      if(initializer.canBeSetUp){
        initializer.setUp();
      }
    }
  }

}