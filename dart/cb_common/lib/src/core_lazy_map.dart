part of core;

class LazyMap<K,V> implements Map{

  Map<K,Function > _internMap = new Map();

  LazyMap.fromFunctionMap(this._internMap);

  bool containsValue(Object value)  => _internMap.containsValue(() => value);

  bool containsKey(Object key) => _internMap.containsKey(key);

  V putIfAbsent(K key, V ifAbsent()) => _internMap.putIfAbsent(key, ()=>()=>ifAbsent())();

  void addAll(Map<K, V> other) {
    _internMap.addAll(new Map<K, Function>.fromIterables(other.keys, other.values.map((V v) => () => v)));
  }

  V remove(Object key)  => _internMap.remove(key)();

  void clear() {
    _internMap.clear();
  }

  void forEach(void f(K key, V value)) {
    _internMap.forEach((K key, Function v) => f(key, v()));
  }

  Iterable<K> get keys => _internMap.keys;

  Iterable<V> get values => _internMap.values.map((Function f) => f());

  int get length => _internMap.length;

  bool get isEmpty => _internMap.isEmpty;

  bool get isNotEmpty => _internMap.isNotEmpty;


  operator [](K key) => _internMap[key];

  operator []=(K key, V value) => _internMap[key] = ()=>value;

}
