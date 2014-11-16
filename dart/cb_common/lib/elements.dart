library elements;

import "dart:async";
import "dart:html";
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
part "src/elements_children_generator.dart";


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

  StreamController<ExpanderElementHandler>
  _onChangeController = new StreamController<ExpanderElementHandler>(),
  _onContractController = new StreamController<ExpanderElementHandler>(),
  _onExpandController = new StreamController<ExpanderElementHandler>();

  Stream<ExpanderElementHandler>
  _onChangeStream, _onContractStream, _onExpandStream;

  factory ExpanderElementHandler(Element element) => _cache.putIfAbsent(element, () => new ExpanderElementHandler._internal(element));

  ExpanderElementHandler._internal(this.element){
    expanderLink.classes.add('expander_link');
    element.insertAdjacentElement("afterBegin", expanderLink);
    expanderLink.onClick.listen((_) => toggle());
    _onChangeStream = _onChangeController.stream.asBroadcastStream();
    _onContractStream = _onContractController.stream.asBroadcastStream();
    _onExpandStream = _onExpandController.stream.asBroadcastStream();
    onExpand.listen(_onChangeController.add);
    onContract.listen(_onChangeController.add);

  }

  bool get expanded => element.classes.contains('expanded');

  void expand() {
    if(expanded){
      return;
    }
    element.classes.add('expanded');

    core.escQueue.add(() {
      if (!expanded) {
        return false;
      }
      contract();
      return !expanded;
    });
    _onExpandController.add(this);
  }

  void contract() {
    if(!expanded){
      return;
    }
    element.classes.remove('expanded');
    _onContractController.add(this);
  }

  void toggle() {
    if (expanded) {
      contract();
    } else {
      expand();
    }
  }

  Stream<ExpanderElementHandler> get onChange => _onChangeStream;
  Stream<ExpanderElementHandler> get onExpand => _onExpandStream;
  Stream<ExpanderElementHandler> get onContract => _onContractStream;

}