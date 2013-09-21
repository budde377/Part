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
part "src/core_keep_alive.dart";
part 'src/core_initializer.dart';
part 'src/core_file_uploader.dart';


int parsePx(String pxString) => int.parse(pxString.replaceAll(new RegExp("[^0-9]"), ""), onError:(_) => 0);

num linearAnimationFunction(double pct, num from, num to) => from + (to - from) * pct;



String sizeToString(int bytes) {
  var s = (bytes <= 102 ? "${bytes} B" : (bytes < 1024 * 1024 / 10 ? "${bytes / 1024} KB" : "${bytes / (1024 * 1024)} MB"));
  var r = new RegExp("([0-9]+\.?[0-9]?[0-9]?)[^ ]*(.+)");
  var m = r.firstMatch(s);
  return m[1] + m[2];
}

bool validMail(String string) => new RegExp(r'^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$',caseSensitive:false).hasMatch(string);
bool nonEmpty(String string) => string.trim().length >0;




class ESCQueue{
  static ESCQueue _cache = new ESCQueue._internal();

  List<Function> _queue = new List<Function>();

  bool enabled = true;

  factory ESCQueue() => _cache;


  ESCQueue._internal(){
    document.onKeyUp.listen((KeyboardEvent kev){
      if(kev.keyCode != 27 || _queue.length == 0 || !enabled){
        return;
      }

      while(_queue.length > 0 && !_queue.removeLast()()){

      }
    });
  }

  void add(bool action()) => _queue.add(action);
}


ESCQueue get escQueue => new ESCQueue();