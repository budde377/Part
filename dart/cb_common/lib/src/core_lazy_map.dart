part of core;

class LazyMap<K, V> implements Map<K, V> {


  Map<K, V> _cache = new Map<K, V>();
  List<K> _keys;
  Function _generator;


  LazyMap.fromGenerator(List<K> keys, V generator(K)): _keys = keys, _generator = generator;


  bool containsValue(Object value) {
    return _cache.containsValue(value) || _keys.fold(false, (bool prev, String key) => prev || this[key] == value);
  }

  bool containsKey(Object key) => _keys.contains(key);

  V putIfAbsent(K key, V ifAbsent()) => _cache.putIfAbsent(key, ifAbsent);

  void addAll(Map<K, V> other) => _cache.addAll(other);

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
    keys.forEach((K e){
      f(e, this[e]);
    });

  }

  Iterable<K> get keys => new List.from(_keys).addAll(_cache.keys);

  Iterable<V> get values {
    var l = new List<V>();
    forEach((_, V v) => l.add(v));
    return l;
  }

  V _generate(Object object) {
    if(_cache.containsKey(object)){
      return _cache[object];
    }
    if(!_keys.contains(object)){
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

  LazyMap<K,V> clone(){
    var m = new LazyMap.fromGenerator(keys, _generator);
    m.addAll(_cache);
    return m;
  }
}
