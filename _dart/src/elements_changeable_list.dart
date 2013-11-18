part of elements;

class ChangeableList {

  final Element list;

  LIElement dragging_li, dragging_over_li;

  static Map<Element, ChangeableList> _cache = new Map<Element, ChangeableList>();

  factory ChangeableList(Element list) => _cache.putIfAbsent(list, ()=>new ChangeableList._internal(list));

  ChangeableList._internal(this.list){
    _setUp();
  }

  void _setUp(){
    list.queryAll("li").forEach((LIElement li){
      li.draggable = true;
    });

    list.onDragOver.listen((_){

    });

    list.onDragStart.listen((MouseEvent ev){
      var target = ev.target;
      if(!list.children.contains(target) || !(target is LIElement)){
        return;
      }
      LIElement li = target;
      ev.dataTransfer..setData("Text", li.hashCode.toString())
                     ..effectAllowed = "move";

      dragging_li = li;
      li.classes.add("dragging");
    });

    list.onDragEnd.listen((MouseEvent ev){
      var target = ev.target;
      if(!list.children.contains(target) || !(target is LIElement)){
        return;
      }
      LIElement li = target;
      dragging_li = null;
      li.classes.remove("dragging");
      dragging_over_li.classes..remove("above")
                              ..remove("below")
                              ..remove("dragging_over");
    });

    list.onDragOver.listen((MouseEvent ev){
      var target = ev.target;
      if(!list.children.contains(target) || !(target is LIElement)){
        return;
      }

      LIElement li = target;
      ev.preventDefault();
      var above = li.marginEdge.top+li.marginEdge.height~/2 >= ev.page.y;

      if(above && !li.classes.contains("above")){
        li.classes..add("above")
                  ..remove("below");
      } else if(!above && !li.classes.contains("below")){
        li.classes..add("below")
                  ..remove("above");
      }

      if(dragging_over_li == li){
        return;
      }
      li.classes..add("dragging_over");
      if(dragging_over_li != null ){
        dragging_over_li.classes.remove("dragging_over");
      }
      dragging_over_li = li;
    });


    list.onDrop.listen((MouseEvent ev){
      var target = ev.target;
      if(!list.children.contains(target) || !(target is LIElement)){
        return;
      }
      LIElement li = target;
      if(li == dragging_li || (li == dragging_li.nextElementSibling && li.classes.contains("above"))
                           || (li == dragging_li.previousElementSibling && li.classes.contains("below"))){
        return;
      }



      if(li.classes.contains("above")){
        list.insertBefore(dragging_li, li);
      } else{
        li.insertAdjacentElement("afterEnd", dragging_li);
      }

      ev.preventDefault();
      list.dispatchEvent(new Event("change", canBubble: true, cancelable:false));


    });


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