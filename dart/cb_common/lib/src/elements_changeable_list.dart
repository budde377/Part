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

  LIElement _getTargetFromEvent(Event ev){
    var target = ev.target;

    if(!(target is Element)){
      return null;
    }

    while(!(target is LIElement) && target != null){
      target = target.parent;
    }

    if(!list.children.contains(target)){
      return null;
    }
    return target;
  }

  void _setUp(){
    list.queryAll("li").forEach((LIElement li){
      li.draggable = true;
    });


    list.onDragStart.listen((MouseEvent ev){
      var target = _getTargetFromEvent(ev);
      if(target == null){
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
      var target = _getTargetFromEvent(ev);
      if(target == null){
        return;
      }

      LIElement li = target;
      _dragging_li = null;
      li.classes.remove("dragging");
      _clearDividers();
    });

    list.onDragOver.listen((MouseEvent ev){
      var target = _getTargetFromEvent(ev);
      if(target == null){
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
      var li = _getTargetFromEvent(ev);
      if(li == null ||  _currently_expanded == null){
        return;
      }

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