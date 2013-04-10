library core;

import "dart:html";

import "dart:json";

import "dart:uri" as Uri;

import "dart:math" as Math;

import "dart:isolate";

import "dart:async";

import "pcre_syntax_checker.dart";

part "src/core_animation.dart";

part "src/core_expand_decoration.dart";

part "src/core_slide_decoration.dart";

part "src/core_input_validator.dart";

part "src/core_validating_form.dart";

part "src/core_keep_alive.dart";

part "src/core_form_decoration.dart";

class BetterSelect {
  SelectElement _element;

  DivElement _container, _arrow, _currentSelection;

  BetterSelect(SelectElement element) {
    this._element = element;
    _initialize();
  }

  String get selectedString => _element.query("option") != null ? _element.selectedOptions.map((Node n) => n.text).join(", ") : "";

  void _initialize() {
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

  static ChangeableList _redeemList(Element listElement){
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

/*
  ChangeableList.unorderetList(UListElement listElement) {
    element = listElement;
    _initialize();
  }

  ChangeableList.orderetList(OListElement listElement) {
    element = listElement;
    _initialize();
  }
*/

  List<LIElement> _findLIList() => element.children.where((Element e) => e.tagName == "LI").toList();

  void _initialize() {
    lis = _findLIList();


    element.on["update_list"].listen((CustomEvent event) {
      element.children.where((Element e) => e.tagName == "LI" && e.classes.contains("new")).forEach((LIElement li) {
        li.classes.remove('new');
        _makeDraggable(li);
        lis = _findLIList();
      });
    });


    lis.forEach((LIElement li) {

      _makeDraggable(li);

    });
  }

  void _makeDraggable(LIElement li) {
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
    lis = _sort(lis, compare);
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

/* Deprecated, use FormDecoration with setUpAJAXSubmit instead
class AJAXForm implements FormDecoration {

  String id;
  Function callbackSuccess, callbackError;

  AJAXForm(FormElement form, String id, [void callbackSuccess(Map map), void callbackError()])  {
    this.id = id;
    this.callbackSuccess = callbackSuccess == null ? (Map map) {} : callbackSuccess;
    this.callbackError = callbackError == null ? () {} : callbackError;
    _initialize();
  }

  void _initialize() {

    HttpRequest req = new HttpRequest();
    req.onReadyStateChange.listen((Event e) {
      if (req.readyState == 4) {
        super.unBlur();
        print(req.responseText);
        try {
          Map responseData = parse(req.responseText);
          callbackSuccess(responseData);

        } catch(e) {
          callbackError();
        }

      }});
    form.onSubmit.listen((Event event) {
      super.blur();
      List<Element> elements = queryAll("textarea, input:not([type=submit]), select");
      req.open(form.method.toUpperCase(), "?ajax=${Uri.encodeUriComponent(id)}");
      req.send(new FormData(form));
      event.preventDefault();
    });
  }

  String generateDataString(Map<String, String> map) {
    return map.keys.map((String s) => "${Uri.encodeUriComponent(s)}=${Uri.encodeUriComponent(map[s])}").join("&");
  }


}

*/


class AJAXRequest {
  String id;

  Map<String, String> data;

  Function callbackSuccess, callbackFailure;

  HttpRequest request;

  AJAXRequest(String id, Map<String, String> data, [void callbackSuccess(Map data), void callbackFailure()]) {
    this.id = id;
    this.data = data;
    this.callbackSuccess = callbackSuccess == null ? (data) {
    } : callbackSuccess;
    this.callbackFailure = callbackFailure == null ? () {
    } : callbackFailure;
  }

  void send() {
    request = new HttpRequest();
    request.onReadyStateChange.listen((Event e) {
      if (request.readyState == 4) {
        print(request.responseText);
        try {
          Map response = parse(request.responseText);
          callbackSuccess(response);
        } catch(e) {
          callbackFailure();
        }

      } else if (request.readyState == 4) {
        callbackFailure();
      }

    });
    request.open("POST", "/?ajax=$id");
    FormData formData = new FormData(new FormElement());
    data.forEach((String key, String value) {
      formData.append(key, value);
    });
    request.send(formData);
  }

}