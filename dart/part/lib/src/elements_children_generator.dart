part of elements;

class ElementChildrenGenerator<K, V extends Element> extends core.Generator<K, V> {

  final Element element;

  static List<Element> _cleanList(List<Element> list, bool check(Element)) {
    list.removeWhere(check);
    return list;
  }

  factory ElementChildrenGenerator(V generator(K), Element elm, K selector(V, Element elm)) => new ElementChildrenGenerator._internal(
      generator,
      elm,
      selector,
          (core.Pair<K, V> pair) => elm.append(pair.v),
          (core.Pair<K, V> pair) => pair.v.remove());

  ElementChildrenGenerator._internal(
      V generator(K),
      Element elm,
      K selector(V, Element elm),
      adder(core.Pair<K, V> pair),
      remover(core.Pair<K, V> pair)
      ) : element = elm, super(
      generator,
      new Map<K, V>.fromIterable(
          _cleanList(elm.children.toList(), (Element element) => selector(element, elm) == null),
          key:(V k) => selector(k, elm))) {
    onBeforeRemove.listen(remover);
    onBeforeAdd.listen(adder);
  }


}


class SortedElementChildrenGenerator<K, V extends Element> extends ElementChildrenGenerator<K, V> {

  factory SortedElementChildrenGenerator.fromDataset(String entry, V generator(K), Element element, K selector(V, Element elm)) => new SortedElementChildrenGenerator.fromValue(
          (Element e1, Element e2) => e1.dataset[entry].compareTo(e2.dataset[entry]),
      generator, element, selector);

  factory SortedElementChildrenGenerator.fromText(V generator(K), Element element, K selector(V, Element elm)) => new SortedElementChildrenGenerator.fromValue(
          (Element e1, Element e2) => e1.text.compareTo(e2.text),
      generator, element, selector);

  factory SortedElementChildrenGenerator.fromKey(int compare(K k1, K k2), V generator(K), Element element, K selector(V, Element elm)) => new SortedElementChildrenGenerator(
          (core.Pair<K, V> p1, core.Pair<K, V> p2) => compare(p1.k, p2.k),
      generator, element, selector);

  factory SortedElementChildrenGenerator.fromValue(int compare(V k1, V k2), V generator(K), Element element, K selector(V, Element elm)) => new SortedElementChildrenGenerator(
          (core.Pair<K, V> p1, core.Pair<K, V> p2) => compare(p1.v, p2.v),
      generator, element, selector);

  SortedElementChildrenGenerator(int compare(core.Pair<K, V> p1, core.Pair<K, V> p2), V generator(K), Element element, K selector(V, Element elm)):
  super._internal(generator, element, selector,
  _updateOrder(element, compare, selector),
      (core.Pair<K, V> pair) => pair.v.remove()) {

    onUpdate.listen(_updateOrder(element, compare, selector));
  }

  static _updateOrder(Element element,
                      int compare(core.Pair<dynamic, Element> p1, core.Pair<dynamic, Element> p2),
                      dynamic selector(Element element, Element key)) {
    return (core.Pair pair) {
      pair.v.remove();
      var child = element.children.firstWhere(
              (Element child) => compare(new core.Pair(selector(child, element), child), pair) > 0,
              orElse: () => null);
      if (child != null) {
        child.insertAdjacentElement('beforeBegin', pair.v);
      } else {
        element.append(pair.v);
      }
    };
  }


}
