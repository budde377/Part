part of elements;

class ChangeableList {
  final Element element;

  LIElement currentlyDragging;

  List<LIElement> lis;

  static Map<Element, ChangeableList> _cache;

  factory ChangeableList.unorderedList(UListElement listElement){
    return _redeemList(listElement);
  }

  factory ChangeableList.orderedList(OListElement listElement){
    return _redeemList(listElement);
  }

  static ChangeableList _redeemList(Element listElement) {
    if (_cache == null) {
      _cache = new Map<Element, ChangeableList>();
    }

    if (_cache.containsKey(listElement)) {
      return _cache[listElement];
    } else {
      var list = new ChangeableList._internal(listElement);
      _cache[listElement] = list;
      return list;
    }
  }

  ChangeableList._internal(this.element){
    _initialize();
  }

  List<LIElement> _findLIList() => element.children.where((Element e) => e.tagName == "LI" && !e.classes.contains('emptyListInfo')).toList();

  void _initialize() {
    lis = _findLIList();


    element.on["update_list"].listen((CustomEvent event) {
      element.children.where((Element e) => e.tagName == "LI" && e.classes.contains("new")).forEach((LIElement li) {
        li.classes.remove('new');
        _makeChangeable(li);
        lis = _findLIList();
      });
    });


    lis.forEach((LIElement li) {

      _makeChangeable(li);

    });
  }

  void refreshLIs() {
    lis = _findLIList();
  }

  void appendLi(LIElement li) {
    element.children.add(li);
    _makeChangeable(li);
    lis = _findLIList();
  }

  void _makeChangeable(LIElement li) {
    Element handle;
    if ((handle = li.children.firstWhere((Element c) => c.classes.contains('handle'), orElse:() => null)) == null) {
      handle = new DivElement();
      handle.classes.add("handle");
      li.children.add(handle);
    }

    handle.onMouseDown.listen((MouseEvent me) {
      int y = 0, startY = me.pageY;
      _resetLI(currentlyDragging);
      li.classes.add("dragging");
      currentlyDragging = li;
      Element shadow = _addShadow(me.pageX, me.pageY);
      shadow.onMouseUp.listen((event) {
        _reorderLIs(lis);
        _removeShadow();
        _resetLI(currentlyDragging);
        currentlyDragging = null;
        y = startY = 0;
      });
      int offset = li.offsetTop;
      int offsetBottom = offset - element.clientHeight + li.clientHeight;
      shadow.onMouseMove.listen((MouseEvent me) {
        if (currentlyDragging == li) {
          int oldY = y;
          y = Math.max(Math.min(startY - me.pageY, offset), offsetBottom);
          li.style.top = "${-y}px";
        }
      });

    });
  }

  void _resetLI(LIElement li) {
    if (li != null) {
      li.classes.remove("dragging");
      li.style.top = "";

    }
  }

  Element _addShadow(int x, int y) {
    _removeShadow();
    DivElement shadow = new DivElement();
    shadow.classes.add("mouseShadow");
    document.body.children.add(shadow);
    shadow.style.left = "${x - 25}px";
    shadow.style.top = "${y - 25}px";
    document.onMouseMove.listen((event) {
      MouseEvent me = event;
      shadow.style.left = "${me.pageX - 25}px";
      shadow.style.top = "${me.pageY - 25}px";

    });
    return shadow;
  }

  void _removeShadow() {
    List<Element> list = queryAll("body>div.mouseShadow");
    list.forEach((e) {
      e.remove();
    });
  }

  void _reorderLIs(List<LIElement> lis) {
    Function compare = (Element e1, Element e2) => e1.offsetTop - e2.offsetTop;
    (lis = lis.toList()).sort(compare);
    bool same = true;
    int i = 0;
    lis.forEach((e) {
      same = same && (i == this.lis.indexOf(e));
      e.remove();
      element.children.add(e);
      i++;
    });
    if (!same) {
      this.lis = lis;
      element.dispatchEvent(new Event("change", canBubble: true, cancelable:false));
    }
  }

}

