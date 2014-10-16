library elements;

import "dart:async";
import "dart:html";
import "dart:convert";
import "dart:math" as Math;

import 'core.dart' as core;
import 'site_classes.dart';
import 'json.dart';

part "src/elements_animation.dart";
part "src/elements_better_select.dart";
part "src/elements_calendar.dart";
part "src/elements_canvas.dart";
part "src/elements_changeable_list.dart";
part "src/elements_color_palette.dart";
part "src/elements_content_editor.dart";
part "src/elements_decoration.dart";
part "src/elements_dialog.dart";
part "src/elements_floating_box.dart";
part "src/elements_form.dart";
part "src/elements_image_editor.dart";
part "src/elements_progressbar.dart";
part "src/elements_move_background_handler.dart";
part "src/elements_file_drop_area_handler.dart";
part "src/elements_dias.dart";


int maxChildrenHeight(Element element) {
  var largestSeen = 0;
  element.children.forEach((Element elm) => largestSeen = Math.max(largestSeen, elm.offsetTop + elm.offsetHeight));
  return largestSeen;
}


class FloatingElementHandler {
  static final Map<Element, FloatingElementHandler> _cache = new Map<Element, FloatingElementHandler>();
  final Element element;
  var _initPosition;

  factory FloatingElementHandler(element) => _cache.putIfAbsent(element, () => new FloatingElementHandler._internal(element));


  FloatingElementHandler._internal(this.element){
    window.onScroll.listen((_) => _update());
    window.onResize.listen((_) => _update());
  }


  void _update() {

    if (element.offsetHeight > window.innerHeight) {
      element.classes.remove("floating");
      return;
    }

    _initPosition = element.parent.documentOffset.y;

    var position = window.scrollY - _initPosition;
    if (position < 0) {
      element.classes.remove("floating");
      return;
    }
    element.classes.add("floating");
  }

}


class ExpanderElementHandler {
  final Element element;
  final Element expanderLink = new DivElement();
  static final _cache = new Map<Element, ExpanderElementHandler>();
  Function _contractFunction = () {
  };

  StreamController<ExpanderElementHandler> _onChangeController = new StreamController<ExpanderElementHandler>(),
  _onContractController = new StreamController<ExpanderElementHandler>(),
  _onExpandController = new StreamController<ExpanderElementHandler>();


  factory ExpanderElementHandler(Element element) => _cache.putIfAbsent(element, () => new ExpanderElementHandler._internal(element));

  ExpanderElementHandler._internal(this.element){
    expanderLink.classes.add('expander_link');
    element.insertAdjacentElement("afterBegin", expanderLink);
    expanderLink.onClick.listen((_) => toggle());
    onExpand.listen(_onContractController.add);
    onContract.listen(_onContractController.add);
  }

  bool get expanded => element.classes.contains('expanded');

  void expand() {
    var contracted = false;
    _contractFunction = () {
      contracted = true;
    };
    element.classes.add('expanded');

    core.escQueue.add(() {
      if (contracted) {
        return false;
      }
      contract();
      return true;
    });
    _onExpandController.add(this);
  }

  void contract() {
    element.classes.remove('expanded');
    _contractFunction();
    _onContractController.add(this);
  }

  void toggle() {
    if (expanded) {
      contract();
    } else {
      expand();
    }
  }

  Stream<ExpanderElementHandler> get onChange => _onChangeController.stream.asBroadcastStream();
  Stream<ExpanderElementHandler> get onExpand => _onExpandController.stream.asBroadcastStream();
  Stream<ExpanderElementHandler> get onContract => _onChangeController.stream.asBroadcastStream();

}