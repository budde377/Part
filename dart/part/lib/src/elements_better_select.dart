part of elements;

class BetterSelect {

  static final Map<SelectElement, BetterSelect> _cached = new Map<SelectElement, BetterSelect>();
  final SelectElement element;

  final DivElement
  container = new DivElement(),
  arrow = new DivElement(),
  currentSelection = new DivElement();

  factory BetterSelect(SelectElement element) {

    if (!_cached.containsKey(element)) {
      _cached[element] = new BetterSelect._internal(element);
    }
    return _cached[element];
  }


  static bool isHandling(SelectElement select) => _cached.containsKey(select);

  BetterSelect._internal(this.element) {
    var width = element.offsetWidth;

    arrow.classes.add("arrow_down");

    currentSelection
      ..classes.add("current_selection")
      ..text = selectedString
      ..children.add(arrow);

    element
      ..classes.add("better_select_select")
      ..insertAdjacentElement("afterEnd", container)
      ..remove();

    container
      ..children.add(element)
      ..children.add(currentSelection)
      ..classes.add("better_select");
    if (!element.classes.contains('no_fix_size')) {
      container.style.width = "${width}px";
    }


    disabled = element.disabled;

    element.onChange.listen((_) => update());


  }

  String get selectedString => element.querySelector("option") != null ? element.selectedOptions.map((Node n) => n.text).join(", ") : "";

  String get value => element.value;

  void set value(String v) {
    element.value = v;
    element.dispatchEvent(new Event("update", canBubble:false));
    update();
  }

  void update() {
    currentSelection.text = selectedString;
    currentSelection.children.add(arrow);
    if (element.disabled) {
      container.classes.add('disabled');
    } else {
      container.classes.remove('disabled');
    }

  }


  bool get disabled => element.disabled;

  void set disabled(bool b) {
    if (b == container.classes.contains('disabled')) {
      return;
    }

    element.disabled = b;
    update();

  }

}
