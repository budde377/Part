part of elements;

class BetterSelect {
  static final Map<SelectElement, BetterSelect> _cached = new Map<SelectElement, BetterSelect>();
  final SelectElement _element;

  DivElement _container, _arrow, _currentSelection;

  factory BetterSelect(SelectElement element) {

    if(!_cached.containsKey(element)){
      _cached[element] = new BetterSelect._internal(element);
    }
    return _cached[element];
  }



  String get selectedString => _element.query("option") != null ? _element.selectedOptions.map((Node n) => n.text).join(", ") : "";

  BetterSelect._internal(this._element) {
    _container = new DivElement();
    _container.classes.add("better_select");
    _currentSelection = new DivElement();
    _currentSelection.classes.add("current_selection");
    _currentSelection.text = selectedString;
    _arrow = new DivElement();
    _arrow.classes.add("arrow_down");
    _currentSelection.children.add(_arrow);
    _element.classes.add("better_select_select");
    _element.insertAdjacentElement("afterEnd", _container);
    _element.remove();
    _container.children.add(_element);
    _container.children.add(_currentSelection);
    _container.style.width = "${_element.offsetWidth}px";
    _element.onChange.listen((event) {
      _currentSelection.text = selectedString;
      _currentSelection.children.add(_arrow);
    });
  }
}
