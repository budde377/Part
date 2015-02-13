part of core;

class LazyMap<K, V> implements Map<K, V> {


  Map<K, V> _cache = new Map<K, V>();
  List<K> _keys;
  Function _generator;

  bool _frozen = false;


  LazyMap.fromGenerator(Iterable<K> keys, V generator(K)): _keys = keys.toList(), _generator = generator;


  bool containsValue(Object value) => _cache.containsValue(value) || _keys.fold(false, (bool prev, K key) => prev || _generate(key) == value);

  bool containsKey(Object key) => keys.contains(key);

  V putIfAbsent(K key, V ifAbsent()) => _cache.putIfAbsent(key, ifAbsent);

  void addAll(Map<K, V> other) {
    _cache.addAll(other);
  }

  V remove(Object key) {

    _generate(key);
    _keys.remove(key);
    return _cache.remove(key);
  }

  void clear() {
    _keys.clear();
    _cache.clear();

  }

  void forEach(void f(K key, V value)) {
    keys.forEach((K e) {
      f(e, _generate(e));
    });

  }

  Iterable<K> get keys {
    var l = new Set<K>.from(_keys);
    l.addAll(_cache.keys);
    return l;
  }

  Iterable<V> get values {
    var l = new List<V>();
    forEach((_, V v) => l.add(v));
    return l;
  }

  V _generate(Object object) {
    if (_cache.containsKey(object)) {
      return _cache[object];
    }
    if (!_keys.contains(object)) {
      return null;
    }
    return _cache[object] = _generator(object);
  }

  int get length => keys.length;

  bool get isEmpty => keys.length == 0;

  bool get isNotEmpty => !isEmpty;

  V operator [] (K key) => _generate(key);

  void operator []= (K key, V value) {
    _cache[key] = value;
  }

}

