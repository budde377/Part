part of core;



class GeneratorPair<K, V> {
  final K k;
  final V v;

  GeneratorPair(this.k, this.v);

}

class Generator<K, V> {

  Map<K, V> _cache;
  Function _generator;

  List<Function> _updaters = <Function>[];

  List<Function> _handlers = <Function>[];

  final StreamController<GeneratorPair<K, V>>
  onInternalAddController = new StreamController<GeneratorPair<K, V>>.broadcast(),
  onInternalRemoveController = new StreamController<GeneratorPair<K, V>>.broadcast(),
  onAddController = new StreamController<GeneratorPair<K, V>>.broadcast(),
  onUpdateController = new StreamController<GeneratorPair<K, V>>.broadcast(),
  onRemoveController = new StreamController<GeneratorPair<K, V>>.broadcast(),
  onEmptyController = new StreamController<GeneratorPair<K, V>>.broadcast(),
  onNotEmptyController = new StreamController<GeneratorPair<K, V>>.broadcast();

  Generator(V generator(K), Map<K, V> cache) : _cache = cache, _generator = generator;

  void addUpdater(void updater(K, V)) {
    _handlers.insert(_updaters.length, updater);
    _updaters.add(updater);
  }

  void addHandler(void handler(K, V)) {
    _cache.forEach(handler);
    _handlers.add(handler);
  }

  Stream<GeneratorPair<K, V>> get onAdd => onAddController.stream;

  Stream<GeneratorPair<K, V>> get onUpdate => onUpdateController.stream;

  Stream<GeneratorPair<K, V>> get onRemove => onRemoveController.stream;

  Stream<GeneratorPair<K, V>> get onEmpty => onRemove.where((_) => size == 0);

  Stream<GeneratorPair<K, V>> get onNotEmpty => onAdd.where((_) => size == 1);

  int get size => _cache.length;

  void update(K k) {
    if (!contains(k)) {
      return;
    }
    var v = this[k];
    _callUpdaters(k, v);
    onUpdateController.add(new GeneratorPair(k, v));

  }

  void _callUpdaters(K k, V v) => _callFunctions(_updaters, k, v);

  void _callHandlers(K k, V v) => _callFunctions(_handlers, k, v);

  void _callFunctions(List<Function> fs, K k, V v) => fs.forEach((Function f) => f(k, v));

  V operator [](K k){
    add(k);
    return _cache[k];
  }

  void add(K k) {
    if (contains(k)) {
      return;
    }
    var v = _cache[k] = _generator(k);
    _callHandlers(k, v);
    onInternalAddController.add(new GeneratorPair<K, V>(k, v));
    onAddController.add(new GeneratorPair<K, V>(k, v));
  }

  void remove(K k) {
    if (!contains(k)) {
      return;
    }
    var v = this[k];
    _cache.remove(k);
    onInternalRemoveController.add(new GeneratorPair<K, V>(k, v));
    onRemoveController.add(new GeneratorPair<K, V>(k, v));
  }


  bool contains(K k) => _cache.containsKey(k);


  void dependsOn(Generator<K, dynamic> generator) {
    generator.onAdd.listen((GeneratorPair p) => add(p.k));
    generator.onRemove.listen((GeneratorPair p) => remove(p.k));
    generator.onUpdate.listen((GeneratorPair p) => update(p.k));

  }


}
