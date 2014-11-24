library function_string_parser;

@MirrorsUsed(targets:const['user_settings', 'site_classes', 'core', 'elements', 'List', 'Map', 'String', 'num', 'int', 'double', 'bool'])
import "dart:mirrors";
import "dart:math" as Math;
import "core.dart";
/**
 * <program>                    = <composite_function_call> | <function_call>
 *
 * <composite_function_call>    = <target><composite_function>
 * <composite_function>         = .<function_chain> | <composite_function>.<function_chain>
 * <function_chain>             = <function_chain><function> | <function>
 *
 * <function_call>              = <target><function>
 * <function>                   = .<name>(<arg_list>) | .<name> () | \[<sap>\]
 * <target>                     = <function_call> | <type>
 * <type>                       = <name> | <type>\<name>
 * <arg_list>                   = <sap> | <sap>, <arg_list> | <named_arg_list>
 * <named_arg_list>             = <named_arg> | <named_arg>, <named_arg_list>
 * <named_arg>                  = <name_nswu> : <sap>
 * <sap>                        = <scalar> | <array> | <program>
 * <array>                      = \[ <all_array_entries>\]
 * <all_array_entries>          = <array_entries> | <named_array_entries>
 * <array_entries>              = <sap> | <sap>, <all_array_entries>
 * <named_array_entries>        = <array_named_entry> | <array_named_entry>, <all_array_entries>
 * <array_named_entry>          = <scalar> => <sap>
 * <scalar>                     = true | false | null | <num> | <string>
 * <name_nswu>                  = [a-zA-Z0-9] | [a-zA-Z0-9]<name>
 * <name>                       = [a-zA-Z_][A-Za-z0-9_]*
 * <num>                        = [+-]? <integer> | <float>
 * <integer>                    = <octal> | <decimal> | <hexadecimal> | <binary>
 * <float>                      = <double_number> | <exp_double_number>
 * <string>                     = *single-quoted-php-string* | *double-quoted-php-string*
 * <decimal>                    = [0-9]+
 * <hexadecimal>                = 0x[0-9A-Fa-f]
 * <octal>                      = 0[0-7]+
 * <binary>                     = 0b[0-1]+
 * <double_number>              = [0-9]*[\.][0-9]*
 * <exp_double_number>          = ([0-9]+|[0-9]*[\.][0-9]*)[eE][+-]?[0-9]+
 */


Function _firstNotNull(Iterable<Function> l) => (String s) => l.fold(null, (prev, Function f) => prev != null ? prev : f(s));

_matchFirstNotNull(Pattern p, String s, f(Match, String)) => _firstNotNull(p.allMatches(s).map((Match m) => (String s) => f(m, s)))(s);

_FSProgram _parseFS(String s) => _parseFSProgram(s.trim());

_FSProgram _parseFSProgram(String s) =>
_firstNotNull([_parseFSCompositeFunctionCall, _parseFSFunctionCall])(s);


_FSCompositeFunctionCall _parseFSCompositeFunctionCall(String s) =>
_matchFirstNotNull(new RegExp(r"\.[\[\.]"), s, (Match m, String s) {
  var t = _parseFSTarget(s.substring(0, m.start).trim());
  if (t == null) {
    return null;
  }
  var f = _parseFSCompositeFunction(s.substring(m.start).trim());
  if (f == null) {
    return null;
  }
  return new _FSCompositeFunctionCall(t, f);
});

_FSTarget _parseFSTarget(String string) =>
_firstNotNull([_parseFSType, _parseFSFunctionCall])(string);

/*
 * <type>                       = <name> | [a-zA-Z_][A-Za-z0-9_\]+[a-zA-Z_]
 */
_FSType _parseFSType(String string) {

  var n = _parseFSName(string);
  if (n != null) {
    return n;
  }

  return _matchFirstNotNull(r"\", string, (Match m, String s) {
    var t = _parseFSType(s.substring(0, m.start).trim());
    if (t == null) {
      return null;
    }

    var n = _parseFSName(s.substring(m.start + 1).trim());

    if (n == null) {
      return null;
    }

    return new _FSTypeBackslashName(t, n);
  });

}

_FSName _parseFSName(String string) => new RegExp(r"^[a-zA-Z_][A-Za-z0-9_]*$", caseSensitive:false).hasMatch(string) ? new _FSName(string) : null;

_FSNotStartingWithUnderscoreName _parseFSNotStartingWithUnderscoreName(String string) {
  if (string.isEmpty) {
    return null;
  }
  if (!new RegExp(r"[a-zA-Z0-9]").hasMatch(string.substring(0, 1))) {
    return null;
  }
  if (string.length == 1) {
    return new _FSNotStartingWithUnderscoreName(string);
  }

  if (_parseFSName(string.substring(1)) == null) {
    return null;
  }

  return new _FSNotStartingWithUnderscoreName(string);

}
/*
  * <composite_function>         = .<function_chain> | <composite_function>.<function_chain>
*/
_FSCompositeFunction _parseFSCompositeFunction(String string) =>
_matchFirstNotNull(new RegExp(r"\.[\[\.]"), string, (Match m, String s) {
  var c = _parseFSFunctionChain(s.substring(m.start + 1).trim());
  if (c == null) {
    return null;
  }
  if (m.start == 0) {
    return new _FSFunctionChainCompositeFunction(c);
  }

  var cf = _parseFSCompositeFunction(s.substring(0, m.start).trim());

  if (cf == null) {
    return null;
  }
  return new _FSFunctionCompositeFunction(cf, c);

});


/*
 * <function_chain>             = <function_chain><function> | <function>
 */

_FSFunctionChain _parseFSFunctionChain(String s) {
  var f = _parseFSFunction(s);
  if (f != null) {
    return f;
  }
  return _matchFirstNotNull(".", s, (Match m, s) {
    var f = _parseFSFunction(s.substring(m.start).trim());
    if (f == null) {
      return null;
    }

    var fc = _parseFSFunctionChain(s.substring(0, m.start).trim());

    if (fc == null) {
      return null;
    }
    return new _FSChainFunctionChain(fc, f);

  });


}


/*
 * <function_call>              = <target><function>
 */

_FSFunctionCall _parseFSFunctionCall(String s) =>
_matchFirstNotNull(new RegExp(r"[\.\[]"), s, (Match m, String s) {
  var t = _parseFSTarget(s.substring(0, m.start).trim());
  if (t == null) {
    return null;
  }
  var f = _parseFSFunction(s.substring(m.start).trim());
  if (f == null) {
    return null;
  }

  return new _FSFunctionCall(t, f);

});

/*
 * <scalar>                     = true | false | null | <num> | <string>
 */
_FSScalar _parseFSScalar(String s) => _firstNotNull([_parseFSBool, _parseFSNull, _parseFSNum, _parseFSString])(s);


_FSBoolScalar _parseFSBool(String s) => s.toLowerCase() == "true" ? new _FSBoolScalar(true) : s.toLowerCase() == "false" ? new _FSBoolScalar(false) : null;


_FSNullScalar _parseFSNull(String s) => s.toLowerCase() == "null" ? new _FSNullScalar() : null;

/*
 * <num>                        = [+-]? <integer> | <float>
 */
_FSNumScalar _parseFSNum(String s) {
  var sign = 1;
  if (s.startsWith("+")) {
    s = s.substring(1).trim();
  } else if (s.startsWith("-")) {
    s = s.substring(1).trim();
    sign = -1;
  }

  _FSNumScalar n = _firstNotNull([_parseFSInteger, _parseFSFloat])(s);
  if (n == null) {
    return null;
  }
  return n.mul(sign);
}
/*
 * <integer>                    = <octal> | <decimal> | <hexadecimal> | <binary>
 */
_FSNumScalar _parseFSInteger(String s) => _firstNotNull([_parseFSOctal, _parseFSDecimal, _parseFSHexadecimal, _parseFSBinary])(s);

/*
 * <float>                      = <double_number> | <exp_double_number>
 */
_FSNumScalar _parseFSFloat(String s) => _firstNotNull([_parseFSDouble, _parseFSExpDouble])(s);

/*
 * <double_number>              = [0-9]*[\.][0-9]*
 */
_FSDoubleNumScalar _parseFSDouble(String s) {
  var m = new RegExp(r"^[0-9]*[\.][0-9]*$").firstMatch(s);
  if (m == null) {
    return null;
  }

  return new _FSDoubleNumScalar(double.parse(m[0]));
}


/*
 * <exp_double_number>          = ([0-9]+|[0-9]*[\.][0-9]*)[eE][+-]?[0-9]+
 */
_FSDoubleNumScalar _parseFSExpDouble(String s) {
  var m = new RegExp(r"^([0-9]+|[0-9]*[\.][0-9]*)[eE][+-]?[0-9]+$").firstMatch(s);
  if (m == null) {
    return null;
  }

  return new _FSDoubleNumScalar(double.parse(m[0]));
}

_FSNumScalar _parseBase(String s, RegExp p, int base) {
  var m = p.firstMatch(s);
  if (m == null) {
    return null;
  }

  return new _FSIntNumScalar(int.parse(m[1], radix:base));
}

/*
 * <octal>                      = 0[0-7]+
 */
_FSNumScalar _parseFSOctal(String s) => _parseBase(s, new RegExp(r"^0([0-7]+)$"), 8);
/*
 * <decimal>                    = [0-9]+
 */
_FSNumScalar _parseFSDecimal(String s) => _parseBase(s, new RegExp(r"^([0-9]+)$"), 10);
/*
 * <hexadecimal>                = 0x[0-9A-Fa-f]
 */
_FSNumScalar _parseFSHexadecimal(String s) => _parseBase(s, new RegExp(r"^0x([0-9a-f]+)$", caseSensitive:false), 16);
/*
 * <binary>                     = 0b[0-1]+
 */
_FSNumScalar _parseFSBinary(String s) => _parseBase(s, new RegExp(r"^0b([0-1]+)$"), 2);

_FSStringScalar _parseFSString(String s) {
  if (!s.startsWith("'") && !s.startsWith('"')) {
    return null;
  }
  var divider = s.substring(0, 1);
  if (!s.endsWith(divider)) {
    return null;
  }


  var i = s.replaceAll(r"\\", "x").replaceAll(r"\" + divider, "x");

  if (!i.startsWith(divider) || !i.endsWith(divider)) {
    return null;
  }

  if (i.substring(1, i.length - 1).contains(divider)) {
    return null;
  }
  if (divider == '"') {
    var map = {
        "n": "\n",
        "r": "\r",
        "t": "\t",
        "v": "\v",
        "e": "\e",
        "f": "\f"
    };
    map.forEach((String key, String value) {
      s = s.replaceAllMapped(new RegExp(r"([^\\])\\" + key), (Match m) => "${m[1]}$value");
    });
    s = s.replaceAllMapped(new RegExp(r"([^\\])\\([0-7]{1,3})"), (Match m) => "${m[1]}" + new String.fromCharCode(int.parse(m[2], radix:8)));
    s = s.replaceAllMapped(new RegExp(r"([^\\])\\x([0-9A-Fa-f]{1,2})"), (Match m) => "${m[1]}" + new String.fromCharCode(int.parse(m[2], radix:16)));
  }
  s = s.replaceAllMapped(new RegExp(r"([^\\])\\\\" + divider), (Match m) => "${m[1]}$divider");
  s = s.replaceAll(r"\\", r"\");
  return new _FSStringScalar(s.substring(1, s.length - 1));
}

/*
 * <function>                   = .<name>(<arg_list>) | .<name> () | \[<scalar>\]
 */
_FSFunction _parseFSFunction(String s) {

  var scalar;
  if (s.startsWith("[") && s.endsWith("]") && (scalar = _parseFSScalarArrayProgram(s.substring(1, s.length - 1).trim())) != null) {
    return new _FSArrayAccessFunction(scalar);
  }


  if (!s.startsWith(".")) {
    return null;
  }

  s = s.substring(1).trim();

  if (!s.endsWith(")")) {
    return null;
  }
  var first = s.indexOf("(");
  if (first < 0) {
    return null;
  }

  var n = _parseFSName(s.substring(0, first).trim());
  if (n == null) {
    return null;
  }

  var a = s.substring(first + 1, s.length - 1).trim();
  if (a.isEmpty) {
    return new _FSNoArgumentFunction(n);
  }

  var args = _parseFSArguments(s.substring(first + 1, s.length - 1).trim());

  if (args == null) {
    return null;
  }

  return new _FSArgumentFunction(n, args);


}

/*
 * <arg_list>                   = <sap> | <sap>, <arg_list> | <named_arg_list>
 */
_FSArgument _parseFSArguments(String s) {
  var sap = _parseFSScalarArrayProgram(s);
  if (sap != null) {
    return new _FSArgument(sap);
  }

  var nal = _parseFSNamedArguments(s);
  if (nal != null) {
    return nal;
  }

  return _matchFirstNotNull(",", s, (Match m, String s) {
    var sap = _parseFSScalarArrayProgram(s.substring(0, m.start).trim());
    if (sap == null) {
      return null;
    }

    var args = _parseFSArguments(s.substring(m.start + 1).trim());

    if (args == null) {
      return null;
    }
    return new _FSArguments(sap, args);

  });

}

/*
 * <named_arg_list>             = <named_arg> | <named_arg>, <named_arg_list>
 */
_FSNamedArgument _parseFSNamedArguments(String s) {
  var arg = _parseFSNamedArgument(s);
  if (arg != null) {
    return arg;
  }

  return _matchFirstNotNull(",", s, (Match m, String s) {
    var arg = _parseFSNamedArgument(s.substring(0, m.start).trim());
    if (arg == null) {
      return null;
    }

    var args = _parseFSNamedArguments(s.substring(m.start + 1).trim());

    if (args == null) {
      return null;
    }
    return new _FSNamedArguments(arg.name, arg.value, args);
  });

}

/*
 * <named_arg>                  = <name_nswu> : <sap>
 */
_FSNamedArgument _parseFSNamedArgument(String s) {
  var first_pos = s.indexOf(":");
  if (first_pos < 0) {
    return null;
  }

  var name = _parseFSNotStartingWithUnderscoreName(s.substring(0, first_pos).trim());
  if (name == null) {
    return null;
  }

  var sap = _parseFSScalarArrayProgram(s.substring(first_pos + 1).trim());
  if (sap == null) {
    return null;
  }

  return new _FSNamedArgument(name, sap);

}

/*
 * <sap>                        = <scalar> | <array> | <program>
 */
_FSScalarArrayProgram _parseFSScalarArrayProgram(String s) => _firstNotNull([_parseFSScalar, _parseFSArray, _parseFSProgram])(s);

/*
 * <array>                      = \[ <array_entries>\]
 */
_FSArray _parseFSArray(String s) {
  if (!s.startsWith("[") || !s.endsWith("]")) {
    return null;
  }

  var entries = _parseFSAllArrayEntries(s.substring(1, s.length - 1).trim());

  if (entries == null) {
    return null;
  }

  return new _FSArray(entries);

}
/*
 * <all_array_entries>          = <array_entries> | <named_array_entries>
 */
_FSArrayEntry _parseFSAllArrayEntries(String s) => _firstNotNull([_parseFSArrayEntries, _parseFSNamedArrayEntries])(s);

/*
 * <array_entries>              = <sap> | <sap>, <all_array_entries>
 */
_FSArrayEntry _parseFSArrayEntries(String s) {
  var sap = _parseFSScalarArrayProgram(s);
  if (sap != null) {
    return new _FSArrayEntry(sap);
  }

  return _matchFirstNotNull(",", s, (Match m, String s) {
    var sap = _parseFSScalarArrayProgram(s.substring(0, m.start).trim());
    if (sap == null) {
      return null;
    }

    var entries = _parseFSAllArrayEntries(s.substring(m.start + 1).trim());
    if (entries == null) {
      return null;
    }
    return new _FSArrayEntries(sap, entries);
  });

}
/*
 * <named_array_entries>        = <array_named_entry> | <array_named_entry>, <all_array_entries>
 */
_FSNamedArrayEntry _parseFSNamedArrayEntries(String s) {
  var ne = _parseFSNamedArrayEntry(s);
  if (ne != null) {
    return ne;
  }

  return _matchFirstNotNull(",", s, (Match m, String s) {
    var ne = _parseFSNamedArrayEntry(s.substring(0, m.start).trim());
    if (ne == null) {
      return null;
    }

    var entries = _parseFSAllArrayEntries(s.substring(m.start + 1).trim());
    if (entries == null) {
      return null;
    }
    return new _FSNamedArrayEntries(ne.key, ne.value, entries);
  });
}
/*
 * <array_named_entry>          = <scalar> => <sap>
 */
_FSNamedArrayEntry _parseFSNamedArrayEntry(String s) {
  var first_index = s.indexOf("=>");
  if (first_index < 0) {
    return null;
  }

  var scalar = _parseFSScalar(s.substring(0, first_index).trim());

  if (scalar == null) {
    return null;
  }

  var sap = _parseFSScalarArrayProgram(s.substring(first_index + 2).trim());
  if (sap == null) {
    return null;
  }
  return new _FSNamedArrayEntry(scalar, sap);

}

abstract class _FSTarget {


}


abstract class _FSType extends _FSTarget {

  String get value;

}

/*
 * <type>                       = <name> | <type>\<name>
 */
class _FSTypeBackslashName extends _FSType {

  final _FSType type;
  final _FSName name;

  String get value => type.value + r"\" + name.value;

  _FSTypeBackslashName(this.type, this.name);


  String toString() => type.toString() + "\\" + name.toString();
}

abstract class _FSProgram extends _FSScalarArrayProgram {
  final _FSTarget target;

  _FSProgram(this.target);

  List<_FSFunctionCall> toFunctionCalls();

  dynamic compute(dynamic computer(_FSProgram)) => computer(this);

}

class _FSCompositeFunctionCall extends _FSProgram implements _FSTarget {
  final _FSCompositeFunction function;

  List<_FSFunctionCall> toFunctionCalls() => function.toFunctionCalls(this.target);

  _FSCompositeFunctionCall(_FSTarget target, this.function) : super(target);

  String toString() => target.toString() + function.toString();

}

class _FSFunctionCall extends _FSProgram implements _FSTarget {

  final _FSFunction function;

  _FSFunctionCall(_FSTarget target, this.function) : super(target);

  String toString() => target.toString() + function.toString();

  List<_FSFunctionCall> toFunctionCalls() => [this];

}

/*
 <composite_function>         = .<function_chain> | <composite_function>.<function_chain>
 */

abstract class _FSCompositeFunction {

  List<_FSFunctionCall> toFunctionCalls(_FSTarget target);

}

class _FSFunctionCompositeFunction extends _FSCompositeFunction {
  final _FSFunctionChain function;
  final _FSCompositeFunction composite;

  _FSFunctionCompositeFunction(this.composite, this.function);

  String toString() => composite.toString() + "." + function.toString();

  List<_FSFunctionCall> toFunctionCalls(_FSTarget target) {
    var l = composite.toFunctionCalls(target);
    l.add(function.toFunctionCall(target));
    return l;
  }

}


class _FSFunctionChainCompositeFunction extends _FSCompositeFunction {
  final _FSFunctionChain function;

  _FSFunctionChainCompositeFunction(this.function);

  String toString() => "." + function.toString();

  List<_FSFunctionCall> toFunctionCalls(_FSTarget target) => [function.toFunctionCall(target)];

}

abstract class _FSFunctionChain {

  _FSFunctionCall toFunctionCall(_FSTarget target);

}

class _FSChainFunctionChain extends _FSFunctionChain {
  final _FSChainFunctionChain chain;
  final _FSFunction function;

  _FSChainFunctionChain(this.chain, this.function);

  String toString() => chain.toString() + function.toString();

  _FSFunctionCall toFunctionCall(_FSTarget target) => new _FSFunctionCall(chain.toFunctionCall(target), function);

}

abstract class _FSFunction extends _FSFunctionChain {

  _FSFunctionCall toFunctionCall(_FSTarget target) => new _FSFunctionCall(target, this);

}

abstract class _FSNamedFunction extends _FSFunction {

  final _FSName name;

  _FSNamedFunction(this.name);

  String toString() => "." + name.toString();

  List<_FSArgument> get argumentList;

  List<_FSArgument> get positionalArgumentList;

  Map<String, _FSArgument> get namedArgumentMap;

  List<dynamic> computedPositionalArgumentList(dynamic converter(_FSProgram));

  Map<String, dynamic> computedNamedArgumentMap(dynamic converter(_FSProgram));

}

class _FSNoArgumentFunction extends _FSNamedFunction {

  _FSNoArgumentFunction(_FSName name) : super(name);

  String toString() => super.toString() + "()";

  List<_FSArgument> get argumentList => [];

  List<_FSArgument> get positionalArgumentList => [];

  Map<String, _FSArgument> get namedArgumentMap => {
  };

  List<dynamic> computedPositionalArgumentList(dynamic converter(_FSProgram)) => positionalArgumentList;

  Map<String, dynamic> computedNamedArgumentMap(dynamic converter(_FSProgram)) => namedArgumentMap;
}

class _FSArgumentFunction extends _FSNamedFunction {

  final _FSArgument argument;

  _FSArgumentFunction(_FSName name, this.argument) : super(name);

  String toString() => super.toString() + "(" + argument.toString() + ")";

  List<_FSArgument> get argumentList => argument.toArgumentList();

  List<_FSArgument> get positionalArgumentList {
    var l = argumentList;
    l.removeWhere((_FSArgument a) => a is _FSNamedArgument);
    l.map((_FSArgument a) => a.value);
    return l;
  }

  Map<String, _FSArgument> get namedArgumentMap {
    var l = argumentList;
    l.removeWhere((_FSArgument a) => a is! _FSNamedArgument);
    var m = new Map.fromIterable(l, key:(_FSNamedArgument a) => a.name.value, value:(_FSNamedArgument a) => a.value);

    return m;

  }


  List<dynamic> computedPositionalArgumentList(dynamic converter(_FSProgram)) => positionalArgumentList.map((_FSArgument a) => a.value.compute(converter)).toList();

  Map<String, dynamic> computedNamedArgumentMap(dynamic converter(_FSProgram)) {
    var m = namedArgumentMap;
    return new Map.fromIterables(m.keys, m.values.map((_FSArgument a) => a.value.compute(converter)));
  }
}


class _FSArrayAccessFunction extends _FSFunction {

  final _FSScalarArrayProgram scalar;


  _FSArrayAccessFunction(this.scalar);

  String toString() => "[" + scalar.toString() + "]";

}

class _FSArgument {

  final _FSScalarArrayProgram value;

  _FSArgument(this.value);

  String toString() => value.toString();

  List<_FSArgument> toArgumentList() => [this];

}

class _FSArguments extends _FSArgument {

  final _FSArgument argument;

  _FSArguments(_FSScalarArrayProgram value, this.argument) : super(value);

  String toString() => super.toString() + ", " + argument.toString();

  List<_FSArgument> toArgumentList() {
    var l = argument.toArgumentList();
    l.insert(0, new _FSArgument(this.value));
    return l;
  }

}

class _FSNamedArgument extends _FSArgument {
  final _FSNotStartingWithUnderscoreName name;

  _FSNamedArgument(this.name, _FSScalarArrayProgram value) : super(value);

  String toString() => name.toString() + " : " + value.toString();

  List<_FSArgument> toArgumentList() => [this];

}

class _FSNamedArguments extends _FSNamedArgument implements _FSArguments {
  final _FSNamedArgument argument;

  _FSNamedArguments(_FSNotStartingWithUnderscoreName name, _FSScalarArrayProgram value, this.argument) : super(name, value);

  String toString() => super.toString() + ", " + argument.toString();

  List<_FSArgument> toArgumentList() {
    var l = argument.toArgumentList();
    l.insert(0, new _FSNamedArgument(this.name, this.value));
    return l;
  }
}


class _FSName extends _FSType {

  final String value;

  _FSName(this.value);

  String toString() => "n{$value}";
}

class _FSNotStartingWithUnderscoreName {
  final String value;

  _FSNotStartingWithUnderscoreName(this.value) ;

  String toString() => "nwou{$value}";
}


abstract class _FSScalarArrayProgram {

  dynamic compute(dynamic computer(_FSProgram));

}

class _FSArray extends _FSScalarArrayProgram {
  final _FSArrayEntry entry;

  List<_FSArrayEntry> get entries => entry.toEntryList();

  bool get isList => entries.every((_FSArrayEntry e) => e is! _FSNamedArrayEntry);

  bool get isMap => !isList;

  Map computeMap(dynamic computer(_FSProgram)) {

    var resultMap = {
    };

    var i = 0;

    entries.forEach((_FSArrayEntry entry) {
      if (entry is _FSNamedArrayEntry) {
        _FSNamedArrayEntry e = entry;
        if (e.key is _FSIntNumScalar) {
          i = Math.max(i, e.key.value);
        }
        resultMap[e.key.value] = e.value.compute(computer);
      } else {
        _FSArrayEntry e = entry;
        resultMap[i] = e.value.compute(computer);
        i++;
      }

    });

    return resultMap;
  }

  List computeList(dynamic computer(_FSProgram)) => computeMap(computer).values.toList();

  dynamic compute(dynamic computer(_FSProgram)) => isList ? computeList(computer) : computeMap(computer);

  _FSArray(this.entry);

  String toString() => "[" + entry.toString() + "]";

}

class _FSArrayEntry {

  final _FSScalarArrayProgram value;

  _FSArrayEntry(this.value);

  String toString() => value.toString();

  List<_FSArrayEntry> toEntryList() => [this];

}

class _FSArrayEntries extends _FSArrayEntry {

  _FSArrayEntry entry;

  _FSArrayEntries(_FSScalarArrayProgram value, this.entry): super(value);

  String toString() => super.toString() + ", " + entry.toString();

  List<_FSArrayEntry> toEntryList() {
    var l = entry.toEntryList();
    l.insert(0, new _FSArrayEntry(value));
    return l;
  }

}


class _FSNamedArrayEntry extends _FSArrayEntry {
  final _FSScalar key;

  _FSNamedArrayEntry(this.key, _FSScalarArrayProgram value) : super(value);

  String toString() => key.toString() + " : " + super.toString();

}

class _FSNamedArrayEntries extends _FSNamedArrayEntry implements _FSArrayEntries {
  _FSArrayEntry entry;

  _FSNamedArrayEntries(_FSScalar key, _FSScalarArrayProgram value, this.entry) : super(key, value);

  String toString() => super.toString() + ", " + entry.toString();

  List<_FSArrayEntry> toEntryList() {
    var l = entry.toEntryList();
    l.insert(0, new _FSNamedArrayEntry(key, value));
    return l;
  }
}

abstract class _FSScalar extends _FSScalarArrayProgram {

  get value;

}


class _FSBoolScalar extends _FSScalar {
  final bool value;

  _FSBoolScalar(this.value);

  String toString() => "b{$value}";

  bool compute(dynamic computer(_FSProgram)) => value;

}

class _FSNullScalar extends _FSScalar {

  final value = null;

  compute(dynamic computer(_FSProgram)) => null;
}

abstract class _FSNumScalar extends _FSScalar {
  final num value;

  _FSNumScalar(this.value);

  _FSNumScalar mul(int);

}


class _FSIntNumScalar implements _FSNumScalar {

  final int value;

  _FSIntNumScalar(this.value);

  String toString() => "int{$value}";

  _FSIntNumScalar mul(int) => new _FSIntNumScalar(int * value);

  int compute(dynamic computer(_FSProgram)) => value;
}


class _FSDoubleNumScalar implements _FSNumScalar {

  final double value;

  _FSDoubleNumScalar(this.value);

  String toString() => "float{$value}";

  _FSDoubleNumScalar mul(int) => new _FSDoubleNumScalar(int * value);

  double compute(dynamic computer(_FSProgram)) => value;

}

class _FSStringScalar extends _FSScalar {
  final String value;

  _FSStringScalar(this.value);

  String toString() => "str{$value}";

  String compute(dynamic computer(_FSProgram)) => value;

}

abstract class _RegisterFunction {

}

class _RegisterNamedFunction implements _RegisterFunction {


  final String name;
  final List positionalArguments;
  final Map<Symbol, dynamic> namedArguments;

  _RegisterNamedFunction(this.name, this.positionalArguments, this.namedArguments);

  _RegisterNamedFunction.from(_FSNamedFunction f, dynamic convert(_FSProgram)) : this(f.name.value, f.computedPositionalArgumentList(convert), () {
    var m = f.computedNamedArgumentMap(convert);
    return new Map.fromIterable(m.keys, key:MirrorSystem.getSymbol, value:(String s) => m[s]);
  }());

}

class _RegisterArrayAccessFunction implements _RegisterFunction {

  final key;

  _RegisterArrayAccessFunction(this.key);

  _RegisterArrayAccessFunction.from(_FSArrayAccessFunction f,  dynamic convert(_FSProgram)) : this(f.scalar.compute(convert));

}


abstract class RegisterHandler {

  Object get instance;

  String get type;

  final Register register;

  bool canRunFunction(String type, _RegisterFunction f, [instance= null]);

  dynamic runFunction(String type, _RegisterFunction f, [instance= null]);

  void remove() {
    register.handlers.remove(this);
  }

  RegisterHandler(this.register);

  String toString() => "RegisterHandler: $type";

}

class TypeRegisterHandler extends RegisterHandler {

  final Type typeT;

  final ClassMirror mirror;

  final String type;

  Object instance;


  TypeRegisterHandler(Register register, Type type, [Object this.instance = null]) : super(register),
  this.typeT = type,
  this.type = MirrorSystem.getName(reflectType(type).simpleName),
  this.mirror = reflectType(type);


  bool _isGetterSetter(_RegisterNamedFunction f) => _getterSetterSymbol(f) != null;


  Symbol _getterSetterSymbol(_RegisterNamedFunction f) {

    if (f.name.length <= 3) {
      return null;
    }
    var setter = false;

    var s1, s2;
    var string = f.name.substring(3);
    if (f.name.startsWith('get')) {

      s1 = MirrorSystem.getSymbol(string[0].toUpperCase() + string.substring(1));
      s2 = MirrorSystem.getSymbol(string[0].toLowerCase() + string.substring(1));

    } else if (f.name.startsWith('set')) {
      s1 = MirrorSystem.getSymbol(string[0].toUpperCase() + string.substring(1) + "=");
      s2 = MirrorSystem.getSymbol(string[0].toLowerCase() + string.substring(1) + "=");
      setter = true;

    } else {
      return null;
    }

    MethodMirror mMirror;
    var s;
    if (mirror.instanceMembers.containsKey(s1)) {
      mMirror = mirror.instanceMembers[s1];
      s = s1;
    } else if (mirror.instanceMembers.containsKey(s2)) {
      mMirror = mirror.instanceMembers[s2];
      s = s2;
    } else {
      return null;
    }


    if (!((setter && mMirror.isSetter) || (!setter && mMirror.isGetter))) {
      return null;
    }

    return setter ? (MirrorSystem.getSymbol((s == s1 ? string[0].toUpperCase() : string[0].toLowerCase()) + string.substring(1))) : s;
  }

  bool canRunFunction(String type, _RegisterFunction function, [instance=null]) {

    instance = instance == null ? this.instance : instance;

    if (instance == null) {
      return false;
    }

    if (type != this.type) {
      return false;
    }
    var mirror = reflect(instance).type; //TODO: Fix when Issue 13440 is done

    if (function is _RegisterArrayAccessFunction) {
      return mirror.instanceMembers.containsKey(MirrorSystem.getSymbol("[]"));

    } else if (function is _RegisterNamedFunction) {
      _RegisterNamedFunction f = function;
      var symbol = MirrorSystem.getSymbol(f.name);
      if (!mirror.instanceMembers.containsKey(symbol)) {
        return _isGetterSetter(function);
      }

      MethodMirror mMirror = mirror.declarations[symbol];
      if (!mMirror.isRegularMethod) {
        return false;
      }

      return true;
    }
    return false;
  }

  dynamic runFunction(String type, _RegisterFunction function, [instance=null]) {

    instance = instance == null ? this.instance : instance;

    if (function is _RegisterArrayAccessFunction) {
      _RegisterArrayAccessFunction f = function;
      return reflect(instance).invoke(MirrorSystem.getSymbol("[]"), [f.key]).reflectee;
    } else {
      _RegisterNamedFunction f = function;
      var s = _getterSetterSymbol(f);
      if (s != null) {
        if (f.name.startsWith('get')) {
          return reflect(instance).getField(s).reflectee;
        }
        return reflect(instance).setField(s, f.positionalArguments[0]).reflectee;

      }
      return reflect(instance).invoke(MirrorSystem.getSymbol(f.name), f.positionalArguments, f.namedArguments).reflectee;
    }
  }


}


class AliasRegisterHandler extends RegisterHandler {


  final String type;

  final RegisterHandler target;

  AliasRegisterHandler(Register register, this.type, this.target) : super(register);

  Object get instance => target.instance;

  bool canRunFunction(String type, _RegisterFunction function, [Object instance]) =>
  target.canRunFunction(type == this.type ? target.type : type, function, instance);

  dynamic runFunction(String type, _RegisterFunction function, [Object instance]) =>
  target.runFunction(type == this.type ? target.type : type, function, instance);


  void removeWithTarget(){
    super.remove();
    target.remove();
  }

}

class SimpleRegisterHandler extends RegisterHandler {


  Object instance;

  final String type;

  final Map<String, Function> functions = new Map<String, Function>();

  Function arrayAccessFunction;

  SimpleRegisterHandler(Register register, this.type, [this.instance = null]) : super(register);

  bool canRunFunction(String type, _RegisterFunction function, [instance]) {
    if (type != this.type) {
      return false;
    }

    if (function is _RegisterNamedFunction) {
      _RegisterNamedFunction f = function;
      return functions.containsKey(f.name);
    }

    if (function is _RegisterArrayAccessFunction) {
      return arrayAccessFunction != null;
    }
    return false;
  }

  dynamic runFunction(String type, _RegisterFunction function, [instance]) {

    instance = instance == null ? this.instance : instance;

    if (function is _RegisterNamedFunction) {
      _RegisterNamedFunction f = function;
      var posArgs = f.positionalArguments;
      posArgs.insert(0, instance);
      return Function.apply(functions[f.name], posArgs, f.namedArguments);
    } else {
      _RegisterArrayAccessFunction f = function;
      return arrayAccessFunction(instance, f.key);
    }
  }


}

class Register {

  int _i = 0;

  static Register _cache;

  final List<RegisterHandler> handlers = new List<RegisterHandler>();

  factory Register() => _cache == null ? _cache = new Register._internal() : _cache;


  Register._internal(){
    addType(String);
    addType(List);
    addType(num);
    addType(int);
    addType(double);
    addType(Map);
    addType(bool);
  }


  AliasRegisterHandler addAlias(String type, RegisterHandler targetHandler) {
    var h = new AliasRegisterHandler(this, type, targetHandler);
    handlers.add(h);
    return h;
  }

  TypeRegisterHandler addType(Type t, [Object instance= null]) {

    var h = new TypeRegisterHandler(this, t, instance);
    handlers.add(h);
    return h;
  }

  SimpleRegisterHandler add(String type, [Object instance= null]) {
    var h = new SimpleRegisterHandler(this, type, instance);
    handlers.add(h);
    return h;
  }


  dynamic _runRegisterFunction(List<String> types, _RegisterFunction function, [instance = null]) {
    var f = handlers.fold(null, (prev, RegisterHandler handler) {
      if (prev != null) {
        return prev;
      }

      var type = types.firstWhere((String s) => handler.canRunFunction(s, function, instance), orElse:() => null);
      if (type == null) {
        return null;
      }

      return () => handler.runFunction(type, function, instance);

    });

    return f == null ? null : f();
  }

  dynamic _runFunction(List<String> types, _FSFunction function, [instance = null]) {
    if (function is _FSNamedFunction) {
      return _runRegisterFunction(types, new _RegisterNamedFunction.from(function, _runProgram), instance);
    } else {
      return _runRegisterFunction(types, new _RegisterArrayAccessFunction.from(function, _runProgram), instance);
    }
  }

  dynamic _runFunctionCall(_FSFunctionCall f, [Object instance = null]) {
    if (f.target is _FSType) {
      _FSType t = f.target;
      return _runFunction([t.value], f.function, instance);
    }

    if (f.target is _FSFunctionCall) {
      var i = _runFunctionCall(f.target);
      return _runFunction(_typesFromInstance(i), f.function, i);
    }

    return null;
  }

  List<String> _buildType(ClassMirror mirror) {
    var l = [MirrorSystem.getName(mirror.simpleName)];
    l.addAll(mirror.superinterfaces.expand(_buildType).toList());

    if (mirror.superclass != null) {
      l.addAll(_buildType(mirror.superclass));
    }
    return l.fold([], (List<String> l, String s) {
      if (!l.contains(s)) {
        l.add(s);
      }
      return l;

    });

  }

  List<String> _typesFromInstance(Object instance) {
    if (instance == null) {
      return [];
    }
    return _buildType(reflect(instance).type);
  }


  dynamic runFunctionString(String s) => _runProgram(_parseFS(s));

  dynamic _runProgram(_FSProgram program) {

    if (program == null) {
      debug("Program was null");
      return null;
    }

    debug("Running program: $program");
    debugger.insertTab();
    var calls = program.toFunctionCalls();
    var lastReturn = null;
    calls.forEach((_FSFunctionCall c) {
      lastReturn = _runFunctionCall(c);
    });
    debugger.removeTab();
    debug("Result: $lastReturn");
    return lastReturn;
  }

}


Register get register => new Register();