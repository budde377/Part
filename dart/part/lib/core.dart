library core;

import "dart:html";
import "dart:math" as Math;
import "dart:async";

import 'json.dart';
import 'dart:convert';

part "src/core_animation.dart";
part "src/core_keep_alive.dart";
part 'src/core_initializer.dart';
part 'src/core_file_uploader.dart';
part 'src/core_function_string_compiler.dart';
part 'src/core_connection.dart';
part 'src/core_response.dart';
part 'src/core_lazy_map.dart';
part 'src/core_generator.dart';

int parseNumber(String pxString) => int.parse(pxString.replaceAll(new RegExp("[^0-9]"), ""), onError:(_) => 0);

num linearAnimationFunction(num pct, num from, num to) => from + (to - from) * pct;


String sizeToString(int bytes) {
  var s = (bytes <= 102 ? "${bytes} B" : (bytes < 1024 * 1024 / 10 ? "${bytes / 1024} KB" : "${bytes / (1024 * 1024)} MB"));
  var r = new RegExp("([0-9]+\.?[0-9]?[0-9]?)[^ ]*(.+)");
  var m = r.firstMatch(s);
  return m[1] + m[2];
}

bool validMail(String string) => new RegExp('^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}\$', caseSensitive:false).hasMatch(string);

bool validUrl(String string) => new RegExp("^http(s)?://.+\.[a-z]{2,3}(/[.0-9]*)?\$", caseSensitive:false).hasMatch(string);

String youtubeVideoIdFromUrl(String string) {
  var firstMatch = new RegExp(r"^http(s)?:\/\/www.youtube.com\/watch\?v=([^&#]+)", caseSensitive:false).firstMatch(string);
  if (firstMatch == null) {
    return null;
  }
  return firstMatch[2];
}

String vimeoVideoIdFromUrl(String string) {
  var firstMatch = new RegExp(r"^http(s)?:\/\/vimeo.com/([^&#]+)", caseSensitive:false).firstMatch(string);
  if (firstMatch == null) {
    return null;
  }
  return firstMatch[2];
}

bool nonEmpty(String string) => string.trim().length > 0;


class ESCQueue {
  static ESCQueue _cache = new ESCQueue._internal();

  List<Function> _queue = new List<Function>();

  bool enabled = true;

  factory ESCQueue() => _cache;


  ESCQueue._internal(){
    document.onKeyUp.listen((KeyboardEvent kev) {
      if (kev.keyCode != 27 || _queue.length == 0 || !enabled) {
        return;
      }

      while (_queue.length > 0 && !_queue.removeLast()()) {

      }
    });
  }

  void add(bool action()) => _queue.add(action);
}

ESCQueue get escQueue => new ESCQueue();

class BodySelectManager {
  static final BodySelectManager _cache = new BodySelectManager._internal();

  factory BodySelectManager() => _cache;

  int _count = 0;

  BodySelectManager._internal();

  void enableSelect() {

    _count = Math.max(0, _count - 1);
    if (_count == 0) {
      body.classes.remove('no_select');
    }
  }

  void disableSelect() {
    _count++;
    body.classes.add("no_select");
  }

}


BodySelectManager get bodySelectManager => new BodySelectManager();


BodyElement get body => querySelector('body');

class NullTreeSanitizer implements NodeTreeSanitizer {
  void sanitizeTree(Node node) {
  }
}


NodeTreeSanitizer get nullNodeTreeSanitizer => new NullTreeSanitizer();


class Position {

  num _x, _y, _z;

  Position({num x, num y, num z}) {
    _x = x;
    _y = y;
    _z = z;
  }


  num get x => _x;

  num get y => _y;

  num get z => _z;

  String toString() {
    return {
        "x":_x, "y":_y, "z":_z
    }.toString();
  }

}


class Debugger {
  static Debugger _instance;

  String _tabs = "";

  bool enabled = false;

  factory Debugger() => _instance == null ? _instance = new Debugger._internal() : _instance;

  Debugger._internal();

  void debug(Object o) {
    if (!enabled) {
      return;
    }
    print("$_tabs$o");
  }

  void insertTab() {
    _tabs += "\t";
  }

  void removeTab() {
    if (numTabs == 0) {
      return;
    }
    _tabs = _tabs.substring(1);
  }

  int get numTabs => _tabs.length;

  String get tabs => _tabs;

}

Debugger get debugger => new Debugger();

Object debug(Object o) {
  debugger.debug(o);
  return o;
}


final double GOLDEN_RATIO = ((Math.sqrt(5) + 1) / 2);


String dayNumberToName(int weekday) {
  var ret;
  switch (weekday) {
    case 1:
      ret = "mandag";
      break;
    case 2:
      ret = "tirsdag";
      break;
    case 3:
      ret = "onsdag";
      break;
    case 4:
      ret = "torsdag";
      break;
    case 5:
      ret = "fredag";
      break;
    case 6:
      ret = "lørdag";
      break;
    case 7:
      ret = "søndag";
      break;
  }
  return ret;
}

String monthNumberToName(int monthNumber) {
  var ret;
  switch (monthNumber) {
    case 1:
      ret = "januar";
      break;
    case 2:
      ret = "februar";
      break;
    case 3:
      ret = "marts";
      break;
    case 4:
      ret = "april";
      break;
    case 5:
      ret = "maj";
      break;
    case 6:
      ret = "juni";
      break;
    case 7:
      ret = "juli";
      break;
    case 8:
      ret = "august";
      break;
    case 9:
      ret = "september";
      break;
    case 10:
      ret = "oktober";
      break;
    case 11:
      ret = "november";
      break;
    case 12:
      ret = "december";
      break;
  }
  return ret;
}

String addLeadingZero(int i) => i < 10 ? "0$i" : "$i";

String dateString(DateTime time, [with_time=true]) {
  var now = new DateTime.now();
  now = new DateTime(now.year, now.month, now.day);
  var diff = now.difference(new DateTime(time.year, time.month, time.day)).inDays;

  var returnString = "";

  switch (diff) {
    case 0:
      returnString = "i dag ";
      break;
    case -1:
      returnString = "i morgen";
      break;
    case 2:
      returnString = "i forgårs";
    break;
    case 1:
      returnString = "i går";
      break;
    case -2:
      returnString = "i overmorgen";
    break;
  default:
      returnString = "${dayNumberToName(time.weekday)} d. ${time.day}. ${monthNumberToName(time.month)} ${time.year} ";

  }



  if (!with_time) {
    return returnString.trim();
  }

  returnString += "kl. ${addLeadingZero(time.hour)}:${addLeadingZero(time.minute)}";

  return returnString.trim();
}


class Pair<K, V> {
  final K k;
  final V v;

  Pair(this.k, this.v);

}

String quoteString(String string, [String quote = '"']) => quote + (string.replaceAll(quote, r"\" + quote)) + quote;


String upperCaseWords(String str) => str.replaceAllMapped(new RegExp("^([a-z\u00E0-\u00FC])|\\s([a-z\u00E0-\u00FC])"), (Match m) => m[0].toUpperCase());

Stream functionStreamGenerator(fun(), Iterable<Stream> streams) {
  StreamController c = new StreamController<bool>();
  var last = fun();
  var f = (_) {
    if (last != fun()) {
      last = !last;
      c.add(last);
    }
  };
  streams.forEach((Stream s) => s.listen(f));
  return c.stream;
}