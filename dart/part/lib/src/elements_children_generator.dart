part of elements;

class ElementChildrenGenerator<K, V extends Element> extends core.Generator<K, V> {

  final Element element;

  static List<Element> _cleanList(List<Element> list, bool check(Element)) {
    list.removeWhere(check);
    return list;
  }

  factory ElementChildrenGenerator(V generator(K), Element elm, K selector(V, Element elm))
  => new ElementChildrenGenerator._internal(
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

  factory SortedElementChildrenGenerator.fromDataset(String entry, V generator(K), Element element, K selector(V, Element elm)) => new SortedElementChildrenGenerator(
          (Element e1, Element e2) => e1.dataset[entry].compareTo(e2.dataset[entry]),
      generator, element, selector);

  factory SortedElementChildrenGenerator.fromText(V generator(K), Element element, K selector(V, Element elm)) => new SortedElementChildrenGenerator(
          (Element e1, Element e2) => e1.text.compareTo(e2.text),
      generator, element, selector);

  SortedElementChildrenGenerator(int compare(V v1, V v2), V generator(K), Element element, K selector(V, Element elm)):
  super._internal(generator, element, selector,
  _updateOrder(element, compare),
      (core.Pair<K, V> pair) => pair.v.remove()) {
    onUpdate.listen(_updateOrder(element, compare));
  }

  static _updateOrder(element, compare) {
    return (core.Pair pair) {
      var child = element.children.firstWhere((Element child) => compare(child, pair.v) >= 0, orElse: () => null);
      if (child != null) {
        child.insertAdjacentElement('afterEnd', pair.v);
      } else {
        element.append(pair.v);
      }
    };
  }


}
