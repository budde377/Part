library elements;

import "dart:async";
import "dart:html";
import "dart:convert";
import "dart:math" as Math;

import 'core.dart';
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


int maxChildrenHeight(Element element) {
  var largestSeen = 0;
  element.children.forEach((Element elm) => largestSeen = Math.max(largestSeen, elm.offsetTop + elm.offsetHeight));
  return largestSeen;
}