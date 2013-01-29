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

class BetterSelect {
  SelectElement _element;
  DivElement _container, _arrow, _currentSelection;

  BetterSelect(SelectElement element) {
    this._element = element;
    _initialize();
  }

  String selectedString() => _element.query("option") != null ? Strings.join(_element.selectedOptions.mappedBy((Node n) => n.text), ", ") : "";

  void _initialize() {
    _container = new DivElement();
    _container.classes.add("better_select");
    _currentSelection = new DivElement();
    _currentSelection.classes.add("current_selection");
    _currentSelection.text = selectedString();
    _arrow = new DivElement();
    _arrow.classes.add("arrow_down");
    _currentSelection.children.add(_arrow);
    _element.classes.add("better_select_select");
    _element.insertAdjacentElement("afterEnd", _container);
    _element.remove();
    _container.children.add(_element);
    _container.children.add(_currentSelection);
    _container.style.width = "${_element.offsetWidth}px";
    _element.on.change.add((event) {
      _currentSelection.text = selectedString();
      _currentSelection.children.add(_arrow);
    });
  }
}

class ChangeableList {
  Element element;
  LIElement currentlyDragging;
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


    element.on["update_list"].add((CustomEvent event) {
      element.queryAll('li.new').forEach((LIElement li){
        li.classes.remove('new');
        _makeDraggable(li);
        lis = element.queryAll("li");
      });
    });


    lis.forEach((LIElement li) {

      _makeDraggable(li);

    });
  }

  void _makeDraggable(LIElement li) {
    Element handle;
    if ((handle = li.query(".handle")) == null) {
      handle = new DivElement();
      handle.classes.add("handle");
      li.children.add(handle);
    }

    handle.on.mouseDown.add((MouseEvent me) {
      int y = 0, startY = me.pageY;
      _resetLI(currentlyDragging);
      li.classes.add("dragging");
      currentlyDragging = li;
      Element shadow = _addShadow(me.pageX, me.pageY);
      shadow.on.mouseUp.add((event) {
        _reorderLIs(lis);
        _removeShadow();
        _resetLI(currentlyDragging);
        currentlyDragging = null;
        y = startY = 0;
      });
      int offset = li.offsetTop;
      int offsetBottom = offset - element.clientHeight + li.clientHeight;
      shadow.on.mouseMove.add((MouseEvent me) {
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
    if (!same) {
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

class BetterForm {
  FormElement form;
  SpanElement filter = new SpanElement();
  static String NOTION_TYPE_ERROR = "error";
  static String NOTION_TYPE_INFORMATION = "info";
  static String NOTION_TYPE_SUCCESS = "success";

  BetterForm(FormElement form) {
    this.form = form;
    filter.classes.add('filter');
  }


  void setNotion(String message, String notion_type) {
    print(notion_type);
    if (notion_type != NOTION_TYPE_SUCCESS && notion_type != NOTION_TYPE_ERROR && notion_type != NOTION_TYPE_INFORMATION) {
      return;
    }

    removeNotion();
    SpanElement notion = new SpanElement();
    notion.classes.add(notion_type);
    notion.classes.add("notion");
    notion.text = message;
    form.insertAdjacentElement("afterBegin", notion);

  }

  void removeNotion() {
    form.queryAll("span.notion").forEach((Element e) {e.remove();});

  }

  void blur() {
    form.classes.add("blur");
    form.insertAdjacentElement("afterBegin", filter);

  }

  void unBlur() {
    form.classes.remove("blur");
    filter.remove();
  }

}

class AJAXForm extends BetterForm {

  String id;
  Function callbackSuccess, callbackError;

  AJAXForm(FormElement form, String id, [void callbackSuccess(Map map), void callbackError()]) : super(form) {
    this.id = id;
    this.callbackSuccess = callbackSuccess == null ? (Map map) {} : callbackSuccess;
    this.callbackError = callbackError == null ? () {} : callbackError;
    _initialize();
  }

  void _initialize() {

    HttpRequest req = new HttpRequest();
    req.on.readyStateChange.add((Event e) {
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
    form.on.submit.add((Event event) {
      super.blur();
      List<Element> elements = queryAll("textarea, input:not([type=submit]), select");
      req.open(form.method.toUpperCase(), "?ajax=${Uri.encodeUriComponent(id)}");
      req.send(new FormData(form));
      event.preventDefault();
    });
  }

  String generateDataString(Map<String, String> map) {
    return Strings.join(map.keys.mappedBy((String s) => "${Uri.encodeUriComponent(s)}=${Uri.encodeUriComponent(map[s])}"), "&");
  }


}



class AJAXRequest {
  String id;
  Map<String, String> data;
  Function callbackSuccess, callbackFailure;
  HttpRequest request;

  AJAXRequest(String id, Map<String, String> data, [void callbackSuccess(Map data), void callbackFailure()]) {
    this.id = id;
    this.data = data;
    this.callbackSuccess = callbackSuccess == null ? (data) {} : callbackSuccess;
    this.callbackFailure = callbackFailure == null ? () {} : callbackFailure;
  }

  void send() {
    request = new HttpRequest();
    request.on.readyStateChange.add((Event e) {
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