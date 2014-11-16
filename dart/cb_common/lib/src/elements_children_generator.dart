part of elements;

class ElementChildrenGenerator<K, V extends Element> extends core.Generator<K, V> {

  final Element element;

  ElementChildrenGenerator(V generator(K), Element elm, K selector(V, Element elm)) : element = elm, super(generator, new Map<K, V>.fromIterable(() {
    var l = elm.children.toList();
    l.removeWhere((Element e) => selector(e, elm) == null);
    return l;
  }(), key:(V k) => selector(k, elm), value:(V v) => v)) {
    onInternalRemoveController.stream.listen((core.GeneratorPair<K, V> pair) => pair.v.remove());
    onInternalAddController.stream.listen((core.GeneratorPair<K, V> pair) => element.append(pair.v));
  }


}
