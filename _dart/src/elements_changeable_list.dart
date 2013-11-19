part of elements;

class ChangeableList {

  final Element list;

  LIElement _dragging_li, _currently_expanded;

  List<LIElement> _dividers = new List<LIElement>();

  static Map<Element, ChangeableList> _cache = new Map<Element, ChangeableList>();

  factory ChangeableList(Element list) => _cache.putIfAbsent(list, ()=>new ChangeableList._internal(list));

  ChangeableList._internal(this.list){
    _setUp();
  }

  void _setUp(){
    list.queryAll("li").forEach((LIElement li){
      li.draggable = true;
    });


    list.onDragStart.listen((MouseEvent ev){
      var target = ev.target;
      if(!list.children.contains(target) || !(target is LIElement)){
        return;
      }
      LIElement li = target;
      ev.dataTransfer..setData("Text", li.hashCode.toString())
                     ..effectAllowed = "move";

      _dragging_li = li;
      li.classes.add("dragging");
      _setUpDividers();
    });

    list.onDragEnd.listen((MouseEvent ev){
      var target = ev.target;
      if(!list.children.contains(target) || !(target is LIElement)){
        return;
      }
      LIElement li = target;
      _dragging_li = null;
      li.classes.remove("dragging");
      _clearDividers();
    });

    list.onDragOver.listen((MouseEvent ev){
      var target = ev.target;
      if(!list.children.contains(target) || !(target is LIElement)){
        return;
      }

      LIElement li = target.classes.contains('divider')?target:
        (target.marginEdge.top+target.marginEdge.height~/2 >= ev.page.y?target.previousElementSibling:target.nextElementSibling);
      ev.preventDefault();

      if(_currently_expanded != null && _currently_expanded != li){
        _currently_expanded.classes.remove("expanded");
      } else if(_currently_expanded == li){
        return;
      }

      li.classes.add('expanded');
      _currently_expanded = li;


    });


    list.onDrop.listen((MouseEvent ev){
      var target = ev.target;

      if(!list.children.contains(target) || !(target is LIElement) || _currently_expanded == null){
        return;
      }

      LIElement li = target;
      if(li == _dragging_li || _dragging_li.nextElementSibling == li || _dragging_li.previousElementSibling == li){
        return;
      }


      list.insertBefore(_dragging_li, _currently_expanded);
      ev.preventDefault();

      _clearDividers();
      list.dispatchEvent(new Event("change", canBubble: true, cancelable:false));


    });


  }

  void _setUpDividers(){
    _dividers = new List<LIElement>();
    list.children.toList().forEach((LIElement li){
      var divider = new LIElement();
      _dividers.add(divider);
      list.insertBefore(divider, li);
      divider.classes.add('divider');
    });
    var li = new LIElement();
    _dividers.add(li);
    li.classes.add('divider');
    list.append(li);
    list.classes.add("incl_dividers");
  }

  void _clearDividers(){
    list.classes.remove("incl_dividers");
    _dividers.toList().forEach((LIElement li){
      li.remove();
      _dividers.remove(li);
    });
  }

  void append(LIElement li){
    list.append(li);
    li.draggable = true;
  }

}

/*
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

  List<Element> _findLIList() => element.children.where((Element e) => e is LIElement && !e.classes.contains('emptyListInfo')).toList();

  void _initialize() {
    lis = _findLIList();


    element.on["update_list"].listen((CustomEvent event) {
      element.children.where((Element e) => e is LIElement && e.classes.contains("new")).forEach((LIElement li) {
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
      int y = 0, startY = me.page.y;
      _resetLI(currentlyDragging);
      li.classes.add("dragging");
      currentlyDragging = li;
      Element shadow = _addShadow(me.page.x, me.page.y);
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
          y = Math.max(Math.min(startY - me.page.y, offset), offsetBottom);
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
      shadow.style.left = "${me.page.x - 25}px";
      shadow.style.top = "${me.page.y - 25}px";

    });
    return shadow;
  }

  void _removeShadow() {
    List<Element> list = queryAll("body>div.mouseShadow");
    list.forEach((e) {
      e.remove();
    });
  }

  void _reorderLIs(List<Element> lis) {
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

*/