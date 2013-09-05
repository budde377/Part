library core;

import "dart:html";
import "dart:json";
import "dart:math" as Math;
import "dart:isolate";
import "dart:async";

import "pcre_syntax_checker.dart";

import "json.dart" as JSON;
import "site_classes.dart" as SiteClasses;

part "src/core_animation.dart";
part "src/core_expand_decoration.dart";
part "src/core_slide_decoration.dart";
part "src/core_input_validator.dart";
part "src/core_validating_form.dart";
part "src/core_keep_alive.dart";
part "src/core_form_decoration.dart";
part 'src/core_dialog.dart';
part 'src/core_progressbar.dart';
part 'src/core_status_bar.dart';
part 'src/core_floating_box.dart';
part 'src/core_initializer.dart';
part 'src/core_content_editor.dart';
part 'src/core_color_palette.dart';
part 'src/core_file_uploader.dart';
part 'src/core_scrollbars.dart';

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

class ESCQueue{
  static ESCQueue _cache = new ESCQueue._internal();

  List<Function> _queue = new List<Function>();

  factory ESCQueue() => _cache;

  ESCQueue._internal(){
    document.onKeyUp.listen((KeyboardEvent kev){
      if(kev.keyCode != 27 || _queue.length == 0 ){
        return;
      }

      while(_queue.length > 0 && !_queue.removeLast()()){

      }
    });
  }

  void add(bool action()) => _queue.add(action);
}


ESCQueue get escQueue => new ESCQueue();