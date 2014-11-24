part of core;


class FunctionStringCompiler {

  static String compile(e) {
    if (e is String) {
      return compileString(e);
    }
    if (e is num) {
      return compileNum(e);
    }
    if (e is bool) {
      return compileBool(e);
    }
    if (e is List) {
      return compileList(e);
    }
    if (e is Map) {
      return compileMap(e);
    }

    if(e is DateTime){
      return compileDateTime(e);
    }
    if(e == null){
      return compileNull();
    }

    return compileString(e.toString());
  }

  static String compileList(List l) => "[" + l.map((e) => compile(e)).join(", ") + "]";

  static String compileMap(Map l) => "[" + l.keys.map((e) => compile(e) + "=>" + compile(l[e])).join(", ") + "]";

  static String compileString(String s) => "'" + (s.replaceAll("'", r"\'")) + "'";

  static String compileNum(num n) => n.toString();

  static String compileNull() => "null";

  static String compileBool(bool b) => b ? "true" : "false";

  static String compileDateTime(DateTime dt) => (dt.millisecondsSinceEpoch ~/1000).toString();
}