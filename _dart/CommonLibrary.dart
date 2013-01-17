library CommonLibrary;
import "dart:html";
import "dart:math" as Math;


class Animation {

  bool exactHasBeenSeen = false, run = false;
  double startTime, duration, currentTime;
  Function animationFunction, callbackFunction;

  Animation(double duration, void animationFunction(double pct), [void callback(bool success)]) {
    this.animationFunction = animationFunction;
    this.duration = duration;
    this.callbackFunction = callback;
  }


  Animation start() {
    run = true;
    window.requestAnimationFrame((time) {
      startTime = time;
      _animate(time);});
    return this;
  }

  void _animate(double time) {
    currentTime = time - startTime;
    if (currentTime <= duration && run) {
      exactHasBeenSeen = currentTime == duration;
      animationFunction(currentTime / duration);
      window.requestAnimationFrame(_animate);
    } else {
      if (!exactHasBeenSeen && run) {
        animationFunction(1);
      }
      if (callbackFunction != null) {
        callbackFunction(run);
      }
      stop();
    }

  }

  Animation stop() {
    run = false;
    return this;
  }


}

class BetterSelect {
  SelectElement element;
  DivElement container, arrow, currentSelection;

  BetterSelect(SelectElement element) {
    this.element = element;
    _initialize();
  }

  void _initialize() {

    container = new DivElement();
    container.classes.add("better_select");
    currentSelection = new DivElement();
    currentSelection.classes.add("current_selection");
    currentSelection.text = element.value;
    arrow = new DivElement();
    arrow.classes.add("arrow_down");
    currentSelection.children.add(arrow);
    element.classes.add("better_select_select");
    element.insertAdjacentElement("afterEnd", container);
    element.remove();
    container.children.add(element);
    container.children.add(currentSelection);
    container.style.width = "${element.offsetWidth}px";
    element.on.change.add((event) {
      currentSelection.text = element.value;
      currentSelection.children.add(arrow);
    });
  }
}

class ChangeableList {
  Element element;
  List<LIElement> lis;

  ChangeableList.unorderetList(UListElement listElement) {
    element = listElement;
    _initialize();
  }

  ChangeableList.orderetList(OListElement listElement) {
    element = listElement;
    _initialize();
  }

  void _initialize() {
    lis = element.queryAll("li");

    Element handle;
    LIElement currentlyDragging;

    lis.forEach((e) {
      int y, startY;
      if ((handle = e.query(".handle")) == null) {
        handle = new DivElement();
        handle.classes.add("handle");
        e.children.add(handle);
      }
      String width, height;
      e.computedStyle.then((style) {
        width = style.width;
        height = style.height;
      });
      handle.on.mouseDown.add((event) {
        _resetLI(currentlyDragging);
        e.classes.add("dragging");

        currentlyDragging = e;
        MouseEvent me = event;
        startY = me.pageY;
        y = 0;
        Element shadow = _addShadow(me.pageX, me.pageY);
        shadow.on.mouseUp.add((event) {
          _reorderLIs(lis);
          _removeShadow();
          _resetLI(currentlyDragging);
          currentlyDragging = null;
          y = startY = 0;
        });
        int offset = e.offsetTop;
        int offsetBottom = offset - element.clientHeight + e.clientHeight;
        shadow.on.mouseMove.add((event) {
          MouseEvent me = event;
          if (currentlyDragging == e) {
            int oldY = y;
            y = Math.max(Math.min(startY - me.pageY, offset), offsetBottom);
            e.style.top = "${-y}px";
          }
        });

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
    document.on.mouseMove.add((event) {
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
    lis = _sort(lis, compare);
    bool same = true;
    int i = 0;
    lis.forEach((e) {
      same = same && (i == this.lis.indexOf(e));
      e.remove();
      element.children.add(e);
      i++;
    });
    if(!same){
      this.lis = lis;
      element.on.change.dispatch(new Event("change", true, false));
    }
  }

  List<dynamic> _sort(List<dynamic> list, int compare(dynamic e1, dynamic e2)) {
    if (list.length > 2) {
      int l1Length = (list.length / 2).floor().toInt();
      List<dynamic> l1 = _sort(list.getRange(0, l1Length), compare), l2 = _sort(list.getRange(l1Length, list.length - l1Length), compare);
      List<dynamic> newList = [];
      dynamic l1First = l1.first, l2First = l2.first;
      while (!l1.isEmpty && !l2.isEmpty) {
        if (compare(l1First, l2First) <= 0) {
          newList.add(l1First);
          if (l1.length > 1) {
            l1 = l1.getRange(1, l1.length - 1);
            l1First = l1.first;
          } else {
            l1 = [];
          }
        } else {
          newList.add(l2First);
          if (l2.length > 1) {
            l2 = l2.getRange(1, l2.length - 1);
            l2First = l2.first;
          } else {
            l2 = [];
          }
        }
      }
      if (!l1.isEmpty) {
        l1.forEach((e) {
          newList.add(e);
        });
      } else if (!l2.isEmpty) {
        l2.forEach((e) {
          newList.add(e);
        });
      }
      return newList;

    } else {
      dynamic e1, e2;
      if (list.length == 2 && compare(e1 = list.first, e2 = list.last) > 0) {
        return [e2, e1];
      }
      return list;
    }
  }


}