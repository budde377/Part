library function_string_parser;

import "dart:mirrors";
import "dart:math" as Math;
/**
 * <program>                    = <composite_function_call> | <function_call>
 *
 * <composite_function_call>    = <target><composite_function>
 * <composite_function>         = .<function_chain> | <composite_function>.<function_chain>
 * <function_chain>             = <function_chain><function> | <function>
 *
 * <function_call>              = <target><function>
 * <function>                   = .<name>(<arg_list>) | .<name> () | \[<scalar>\]
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
 * <string>                     = *single-quoted-string* | *double-quoted-string*
 * <decimal>                    = [0-9]+
 * <hexadecimal>                = 0x[0-9A-Fa-f]
 * <octal>                      = 0[0-7]+
 * <binary>                     = 0b[0-1]+
 * <double_number>              = [0-9]*[\.][0-9]*
 * <exp_double_number>          = ([0-9]+|[0-9]*[\.][0-9]*)[eE][+-]?[0-9]+
 */


Function _firstNotNull(Iterable<Function> l) => (String s) => l.fold(null, (prev, Function f) => prev != null ? prev : f(s));

_matchFirstNotNull(Pattern p, String s, f(Match, String)) => _firstNotNull(p.allMatches(s).map((Match m) => (String s) => f(m, s)))(s);

FSProgram parseFS(String s) => _parseFSProgram(s.trim());

FSProgram _parseFSProgram(String s) =>
_firstNotNull([_parseFSCompositeFunctionCall, _parseFSFunctionCall])(s);


FSCompositeFunctionCall _parseFSCompositeFunctionCall(String s) =>
_matchFirstNotNull(new RegExp(r"\.[\[\.]"), s, (Match m, String s) {
  var t = _parseFSTarget(s.substring(0, m.start).trim());
  if (t == null) {
    return null;
  }
  var f = _parseFSCompositeFunction(s.substring(m.start).trim());
  if (f == null) {
    return null;
  }
  return new FSCompositeFunctionCall(t, f);
});

FSTarget _parseFSTarget(String string) =>
_firstNotNull([_parseFSType, _parseFSFunctionCall])(string);

/*
 * <type>                       = <name> | [a-zA-Z_][A-Za-z0-9_\]+[a-zA-Z_]
 */
FSType _parseFSType(String string) {

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

    return new FSTypeBackslashName(t, n);
  });

}

FSName _parseFSName(String string) => new RegExp(r"^[a-zA-Z_][A-Za-z0-9_]*$", caseSensitive:false).hasMatch(string) ? new FSName(string) : null;

FSNotStartingWithUnderscoreName _parseFSNotStartingWithUnderscoreName(String string) {
  if (string.isEmpty) {
    return null;
  }
  if (!new RegExp(r"[a-zA-Z0-9]").hasMatch(string.substring(0, 1))) {
    return null;
  }
  if (string.length == 1) {
    return new FSNotStartingWithUnderscoreName(string);
  }

  if (_parseFSName(string.substring(1)) == null) {
    return null;
  }

  return new FSNotStartingWithUnderscoreName(string);

}
/*
  * <composite_function>         = .<function_chain> | <composite_function>.<function_chain>
*/
FSCompositeFunction _parseFSCompositeFunction(String string) =>
_matchFirstNotNull(new RegExp(r"\.[\[\.]"), string, (Match m, String s) {
  var c = _parseFSFunctionChain(s.substring(m.start + 1).trim());
  if (c == null) {
    return null;
  }
  if (m.start == 0) {
    return new FSFunctionChainCompositeFunction(c);
  }

  var cf = _parseFSCompositeFunction(s.substring(0, m.start).trim());

  if (cf == null) {
    return null;
  }
  return new FSFunctionCompositeFunction(cf, c);

});


/*
 * <function_chain>             = <function_chain><function> | <function>
 */

FSFunctionChain _parseFSFunctionChain(String s) {
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
    return new FSChainFunctionChain(fc, f);

  });


}


/*
 * <function_call>              = <target><function>
 */

FSFunctionCall _parseFSFunctionCall(String s) =>
_matchFirstNotNull(new RegExp(r"[\.\[]"), s, (Match m, String s) {
  var t = _parseFSTarget(s.substring(0, m.start).trim());
  if (t == null) {
    return null;
  }

  var f = _parseFSFunction(s.substring(m.start).trim());
  if (f == null) {
    return null;
  }
  return new FSFunctionCall(t, f);

});

/*
 * <scalar>                     = true | false | null | <num> | <string>
 */
FSScalar _parseFSScalar(String s) => _firstNotNull([_parseFSBool, _parseFSNull, _parseFSNum, _parseFSString])(s);


FSBoolScalar _parseFSBool(String s) => s.toLowerCase() == "true" ? new FSBoolScalar(true) : s.toLowerCase() == "false" ? new FSBoolScalar(false) : null;


FSNullScalar _parseFSNull(String s) => s.toLowerCase() == "null" ? new FSNullScalar() : null;

/*
 * <num>                        = [+-]? <integer> | <float>
 */
FSNumScalar _parseFSNum(String s) {
  var sign = 1;
  if (s.startsWith("+")) {
    s = s.substring(1).trim();
  } else if (s.startsWith("-")) {
    s = s.substring(1).trim();
    sign = -1;
  }

  FSNumScalar n = _firstNotNull([_parseFSInteger, _parseFSFloat])(s);
  if (n == null) {
    return null;
  }
  return n.mul(sign);
}
/*
 * <integer>                    = <octal> | <decimal> | <hexadecimal> | <binary>
 */
FSNumScalar _parseFSInteger(String s) => _firstNotNull([_parseFSOctal, _parseFSDecimal, _parseFSHexadecimal, _parseFSBinary])(s);

/*
 * <float>                      = <double_number> | <exp_double_number>
 */
FSNumScalar _parseFSFloat(String s) => _firstNotNull([_parseFSDouble, _parseFSExpDouble])(s);

/*
 * <double_number>              = [0-9]*[\.][0-9]*
 */
FSDoubleNumScalar _parseFSDouble(String s) {
  var m = new RegExp(r"^[0-9]*[\.][0-9]*$").firstMatch(s);
  if (m == null) {
    return null;
  }

  return new FSDoubleNumScalar(double.parse(m[0]));
}


/*
 * <exp_double_number>          = ([0-9]+|[0-9]*[\.][0-9]*)[eE][+-]?[0-9]+
 */
FSDoubleNumScalar _parseFSExpDouble(String s) {
  var m = new RegExp(r"^([0-9]+|[0-9]*[\.][0-9]*)[eE][+-]?[0-9]+$").firstMatch(s);
  if (m == null) {
    return null;
  }

  return new FSDoubleNumScalar(double.parse(m[0]));
}

FSNumScalar _parseBase(String s, RegExp p, int base) {
  var m = p.firstMatch(s);
  if (m == null) {
    return null;
  }

  return new FSIntNumScalar(int.parse(m[1], radix:base));
}

/*
 * <octal>                      = 0[0-7]+
 */
FSNumScalar _parseFSOctal(String s) => _parseBase(s, new RegExp(r"^0([0-7]+)$"), 8);
/*
 * <decimal>                    = [0-9]+
 */
FSNumScalar _parseFSDecimal(String s) => _parseBase(s, new RegExp(r"^([0-9]+)$"), 10);
/*
 * <hexadecimal>                = 0x[0-9A-Fa-f]
 */
FSNumScalar _parseFSHexadecimal(String s) => _parseBase(s, new RegExp(r"^0x([0-9a-f]+)$", caseSensitive:false), 16);
/*
 * <binary>                     = 0b[0-1]+
 */
FSNumScalar _parseFSBinary(String s) => _parseBase(s, new RegExp(r"^0b([0-1]+)$"), 2);

FSStringScalar _parseFSString(String s) {
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
  return new FSStringScalar(s.substring(1, s.length - 1));
}

/*
 * <function>                   = .<name>(<arg_list>) | .<name> () | \[<scalar>\]
 */
FSFunction _parseFSFunction(String s) {

  var scalar;
  if (s.startsWith("[") && s.endsWith("]") && (scalar = _parseFSScalar(s.substring(1, s.length - 1).trim())) != null) {
    return new FSArrayAccessFunction(scalar);
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
    return new FSNoArgumentFunction(n);
  }

  var args = _parseFSArguments(s.substring(first + 1, s.length - 1).trim());

  if (args == null) {
    return null;
  }

  return new FSArgumentFunction(n, args);


}

/*
 * <arg_list>                   = <sap> | <sap>, <arg_list> | <named_arg_list>
 */
FSArgument _parseFSArguments(String s) {
  var sap = _parseFSScalarArrayProgram(s);
  if (sap != null) {
    return new FSArgument(sap);
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
    return new FSArguments(sap, args);

  });

}

/*
 * <named_arg_list>             = <named_arg> | <named_arg>, <named_arg_list>
 */
FSNamedArgument _parseFSNamedArguments(String s) {
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
    return new FSNamedArguments(arg.name, arg.value, args);
  });

}

/*
 * <named_arg>                  = <name_nswu> : <sap>
 */
FSNamedArgument _parseFSNamedArgument(String s) {
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

  return new FSNamedArgument(name, sap);

}

/*
 * <sap>                        = <scalar> | <array> | <program>
 */
FSScalarArrayProgram _parseFSScalarArrayProgram(String s) => _firstNotNull([_parseFSScalar, _parseFSArray, _parseFSProgram])(s);

/*
 * <array>                      = \[ <array_entries>\]
 */
FSArray _parseFSArray(String s) {
  if (!s.startsWith("[") || !s.endsWith("]")) {
    return null;
  }

  var entries = _parseFSAllArrayEntries(s.substring(1, s.length - 1).trim());

  if (entries == null) {
    return null;
  }

  return new FSArray(entries);

}
/*
 * <all_array_entries>          = <array_entries> | <named_array_entries>
 */
FSArrayEntry _parseFSAllArrayEntries(String s) => _firstNotNull([_parseFSArrayEntries, _parseFSNamedArrayEntries])(s);

/*
 * <array_entries>              = <sap> | <sap>, <all_array_entries>
 */
FSArrayEntry _parseFSArrayEntries(String s) {
  var sap = _parseFSScalarArrayProgram(s);
  if (sap != null) {
    return new FSArrayEntry(sap);
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
    return new FSArrayEntries(sap, entries);
  });

}
/*
 * <named_array_entries>        = <array_named_entry> | <array_named_entry>, <all_array_entries>
 */
FSNamedArrayEntry _parseFSNamedArrayEntries(String s) {
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
    return new FSNamedArrayEntries(ne.key, ne.value, entries);
  });
}
/*
 * <array_named_entry>          = <scalar> => <sap>
 */
FSNamedArrayEntry _parseFSNamedArrayEntry(String s) {
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
  return new FSNamedArrayEntry(scalar, sap);

}

abstract class FSTarget {


}


abstract class FSType extends FSTarget {

  String get value;

}

/*
 * <type>                       = <name> | <type>\<name>
 */
class FSTypeBackslashName extends FSType {

  final FSType type;
  final FSName name;

  String get value => type.value + r"\" + name.value;

  FSTypeBackslashName(this.type, this.name);


  String toString() => type.toString() + "\\" + name.toString();
}

abstract class FSProgram extends FSScalarArrayProgram {
  final FSTarget target;

  FSProgram(this.target);

  List<FSFunctionCall> toFunctionCalls();

  dynamic compute(dynamic computer(FSProgram)) => computer(this);

}

class FSCompositeFunctionCall extends FSProgram implements FSTarget {
  final FSCompositeFunction function;

  List<FSFunctionCall> toFunctionCalls() => function.toFunctionCalls(this.target);

  FSCompositeFunctionCall(FSTarget target, this.function) : super(target);

  String toString() => target.toString() + function.toString();

}

class FSFunctionCall extends FSProgram implements FSTarget {

  final FSFunction function;

  FSFunctionCall(FSTarget target, this.function) : super(target);

  String toString() => target.toString() + function.toString();

  List<FSFunctionCall> toFunctionCalls() => [this];

}

/*
 <composite_function>         = .<function_chain> | <composite_function>.<function_chain>
 */

abstract class FSCompositeFunction {

  List<FSFunctionCall> toFunctionCalls(FSTarget target);

}

class FSFunctionCompositeFunction extends FSCompositeFunction {
  final FSFunctionChain function;
  final FSCompositeFunction composite;

  FSFunctionCompositeFunction(this.composite, this.function);

  String toString() => composite.toString() + "." + function.toString();

  List<FSFunctionCall> toFunctionCalls(FSTarget target) {
    var l = composite.toFunctionCalls(target);
    l.add(function.toFunctionCall(target));
    return l;
  }

}


class FSFunctionChainCompositeFunction extends FSCompositeFunction {
  final FSFunctionChain function;

  FSFunctionChainCompositeFunction(this.function);

  String toString() => "." + function.toString();

  List<FSFunctionCall> toFunctionCalls(FSTarget target) => [function.toFunctionCall(target)];

}

abstract class FSFunctionChain {

  FSFunctionCall toFunctionCall(FSTarget target);

}

class FSChainFunctionChain extends FSFunctionChain {
  final FSChainFunctionChain chain;
  final FSFunction function;

  FSChainFunctionChain(this.chain, this.function);

  String toString() => chain.toString() + function.toString();

  FSFunctionCall toFunctionCall(FSTarget target) => new FSFunctionCall(chain.toFunctionCall(target), function);

}

abstract class FSFunction extends FSFunctionChain {

  FSFunctionCall toFunctionCall(FSTarget target) => new FSFunctionCall(target, this);

}

abstract class FSNamedFunction extends FSFunction {

  final FSName name;

  FSNamedFunction(this.name);

  String toString() => "." + name.toString();

  List<FSArgument> get argumentList;

  List<FSArgument> get positionalArgumentList;

  Map<String, FSArgument> get namedArgumentMap;

  List<dynamic> computedPositionalArgumentList(dynamic converter(FSProgram));

  Map<String, dynamic> computedNamedArgumentMap(dynamic converter(FSProgram));

}

class FSNoArgumentFunction extends FSNamedFunction {

  FSNoArgumentFunction(FSName name) : super(name);

  String toString() => super.toString() + "()";

  List<FSArgument> get argumentList => [];

  List<FSArgument> get positionalArgumentList => [];

  Map<String, FSArgument> get namedArgumentMap => {
  };

  List<dynamic> computedPositionalArgumentList(dynamic converter(FSProgram)) => positionalArgumentList;

  Map<String, dynamic> computedNamedArgumentMap(dynamic converter(FSProgram)) => namedArgumentMap;
}

class FSArgumentFunction extends FSNamedFunction {

  final FSArgument argument;

  FSArgumentFunction(FSName name, this.argument) : super(name);

  String toString() => super.toString() + "(" + argument.toString() + ")";

  List<FSArgument> get argumentList => argument.toArgumentList();

  List<FSArgument> get positionalArgumentList {
    var l = argumentList;
    l.removeWhere((FSArgument a) => a is FSNamedArgument);
    l.map((FSArgument a) => a.value);
    return l;
  }

  Map<String, FSArgument> get namedArgumentMap {
    var l = argumentList;
    l.removeWhere((FSArgument a) => a is! FSNamedArgument);
    var m = new Map.fromIterable(l, key:(FSNamedArgument a) => a.name.value, value:(FSNamedArgument a) => a.value);

    return m;

  }


  List<dynamic> computedPositionalArgumentList(dynamic converter(FSProgram)) => positionalArgumentList.map((FSArgument a) => a.value.compute(converter)).toList();

  Map<String, dynamic> computedNamedArgumentMap(dynamic converter(FSProgram)) {
    var m = namedArgumentMap;
    return new Map.fromIterables(m.keys, m.values.map((FSArgument a) => a.value.compute(converter)));
  }
}


class FSArrayAccessFunction extends FSFunction {

  final FSScalar scalar;


  FSArrayAccessFunction(this.scalar);

  String toString() => "[" + scalar.toString() + "]";

}

class FSArgument {

  final FSScalarArrayProgram value;

  FSArgument(this.value);

  String toString() => value.toString();

  List<FSArgument> toArgumentList() => [this];

}

class FSArguments extends FSArgument {

  final FSArgument argument;

  FSArguments(FSScalarArrayProgram value, this.argument) : super(value);

  String toString() => super.toString() + ", " + argument.toString();

  List<FSArgument> toArgumentList() {
    var l = argument.toArgumentList();
    l.insert(0, new FSArgument(this.value));
    return l;
  }

}

class FSNamedArgument extends FSArgument {
  final FSNotStartingWithUnderscoreName name;

  FSNamedArgument(this.name, FSScalarArrayProgram value) : super(value);

  String toString() => name.toString() + " : " + value.toString();

  List<FSArgument> toArgumentList() => [this];

}

class FSNamedArguments extends FSNamedArgument implements FSArguments {
  final FSNamedArgument argument;

  FSNamedArguments(FSNotStartingWithUnderscoreName name, FSScalarArrayProgram value, this.argument) : super(name, value);

  String toString() => super.toString() + ", " + argument.toString();

  List<FSArgument> toArgumentList() {
    var l = argument.toArgumentList();
    l.insert(0, new FSNamedArgument(this.name, this.value));
    return l;
  }
}


class FSName extends FSType {

  final String value;

  FSName(this.value);

  String toString() => "n{$value}";
}

class FSNotStartingWithUnderscoreName {
  final String value;

  FSNotStartingWithUnderscoreName(this.value) ;

  String toString() => "nwou{$value}";
}


abstract class FSScalarArrayProgram {

  dynamic compute(dynamic computer(FSProgram));

}

class FSArray extends FSScalarArrayProgram {
  final FSArrayEntry entry;

  List<FSArrayEntry> get entries => entry.toEntryList();

  bool get isList => entries.every((FSArrayEntry e) => e is! FSNamedArrayEntry);

  bool get isMap => !isList;

  Map computeMap(dynamic computer(FSProgram)) {

    var resultMap = {
    };

    var i = 0;

    entries.forEach((FSArrayEntry entry) {
      if (entry is FSNamedArrayEntry) {
        FSNamedArrayEntry e = entry;
        if (e.key is FSIntNumScalar) {
          i = Math.max(i, e.key.value);
        }
        resultMap[e.key.value] = e.value.compute(computer);
      } else {
        FSArrayEntry e = entry;
        resultMap[i] = e.value.compute(computer);
        i++;
      }

    });

    return resultMap;
  }

  List computeList(dynamic computer(FSProgram)) => computeMap(computer).values;

  dynamic compute(dynamic computer(FSProgram)) => isList ? computeList(computer) : computeMap(computer);

  FSArray(this.entry);

  String toString() => "[" + entry.toString() + "]";

}

class FSArrayEntry {

  final FSScalarArrayProgram value;

  FSArrayEntry(this.value);

  String toString() => value.toString();

  List<FSArrayEntry> toEntryList() => [this];

}

class FSArrayEntries extends FSArrayEntry {

  FSArrayEntry entry;

  FSArrayEntries(FSScalarArrayProgram value, this.entry): super(value);

  String toString() => super.toString() + ", " + entry.toString();

  List<FSArrayEntry> toEntryList() {
    var l = entry.toEntryList();
    l.insert(0, new FSArrayEntry(value));
    return l;
  }

}


class FSNamedArrayEntry extends FSArrayEntry {
  final FSScalar key;

  FSNamedArrayEntry(this.key, FSScalarArrayProgram value) : super(value);

  String toString() => key.toString() + " : " + super.toString();

}

class FSNamedArrayEntries extends FSNamedArrayEntry implements FSArrayEntries {
  FSArrayEntry entry;

  FSNamedArrayEntries(FSScalar key, FSScalarArrayProgram value, this.entry) : super(key, value);

  String toString() => super.toString() + ", " + entry.toString();

  List<FSArrayEntry> toEntryList() {
    var l = entry.toEntryList();
    l.insert(0, new FSNamedArrayEntry(key, value));
    return l;
  }
}

abstract class FSScalar extends FSScalarArrayProgram {

  get value;

}


class FSBoolScalar extends FSScalar {
  final bool value;

  FSBoolScalar(this.value);

  String toString() => "b{$value}";

  bool compute(dynamic computer(FSProgram)) => value;

}

class FSNullScalar extends FSScalar {

  final value = null;

  compute(dynamic computer(FSProgram)) => null;
}

abstract class FSNumScalar extends FSScalar {
  final num value;

  FSNumScalar(this.value);

  FSNumScalar mul(int);

}


class FSIntNumScalar implements FSNumScalar {

  final int value;

  FSIntNumScalar(this.value);

  String toString() => "int{$value}";

  FSIntNumScalar mul(int) => new FSIntNumScalar(int * value);

  int compute(dynamic computer(FSProgram)) => value;
}


class FSDoubleNumScalar implements FSNumScalar {

  final double value;

  FSDoubleNumScalar(this.value);

  String toString() => "float{$value}";

  FSDoubleNumScalar mul(int) => new FSDoubleNumScalar(int * value);

  double compute(dynamic computer(FSProgram)) => value;

}

class FSStringScalar extends FSScalar {
  final String value;

  FSStringScalar(this.value);

  String toString() => "str{$value}";

  String compute(dynamic computer(FSProgram)) => value;

}

abstract class FSRegisterFunction {

}

class FSRegisterNamedFunction implements FSRegisterFunction {


  final String name;
  final List positionalArguments;
  final Map<Symbol, dynamic> namedArguments;

  FSRegisterNamedFunction(this.name, this.positionalArguments, this.namedArguments);

  FSRegisterNamedFunction.from(FSNamedFunction f, dynamic convert(FSProgram)) : this(f.name.value, f.computedPositionalArgumentList(convert), () {
    var m = f.computedNamedArgumentMap(convert);
    return new Map.fromIterable(m.keys, key:MirrorSystem.getSymbol, value:(String s) => m[s]);
  }());

}

class FSRegisterArrayAccessFunction implements FSRegisterFunction {

  final key;

  FSRegisterArrayAccessFunction(this.key);

  FSRegisterArrayAccessFunction.from(FSArrayAccessFunction f) : this(f.scalar.value);

}


abstract class FSRegisterHandler {

  final Object instance;

  final FSRegister register;

  bool canRunFunction(String type, FSRegisterFunction f, [instance= null]);

  dynamic runFunction(String type, FSRegisterFunction f, [instance= null]);

  void remove() {
    register.handlers.remove(this);
  }

  FSRegisterHandler(this.register, this.instance);


}

class TypeFSRegisterHandler extends FSRegisterHandler {

  final Type type;
  final ClassMirror mirror;


  TypeFSRegisterHandler(FSRegister register, Type type, [Object instance = null]) : super(register, instance), this.type = type, this.mirror = reflectType(type);


  bool _isGetterSetter(FSRegisterNamedFunction f) => _getterSetterSymbol(f) != null;


  Symbol _getterSetterSymbol(FSRegisterNamedFunction f) {

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

    print(mMirror);

    if (!((setter && mMirror.isSetter) || (!setter && mMirror.isGetter))) {
      return null;
    }

    return setter?(MirrorSystem.getSymbol((s == s1?string[0].toUpperCase():string[0].toLowerCase()) + string.substring(1))):s;
  }

  bool canRunFunction(String type, FSRegisterFunction function, [instance=null]) {

    instance = instance == null ? this.instance : instance;

    if (instance == null) {
      return false;
    }

    print("name: ${MirrorSystem.getName(mirror.simpleName)}");

    if (type != MirrorSystem.getName(mirror.simpleName)) {
      return false;
    }


    if (function is FSRegisterArrayAccessFunction) {
      return mirror.instanceMembers.containsKey(MirrorSystem.getSymbol("[]"));

    } else if (function is FSRegisterNamedFunction) {
      FSRegisterNamedFunction f = function;
      var symbol = MirrorSystem.getSymbol(f.name);
      if (!mirror.instanceMembers.containsKey(symbol)) {
        return _isGetterSetter(function);;
      }

      MethodMirror mMirror = mirror.instanceMembers[symbol];
      if (!mMirror.isRegularMethod) {
        return false;
      }

      return true;
    }
    return false;
  }

  dynamic runFunction(String type, FSRegisterFunction function, [instance=null]) {
    instance = instance == null ? this.instance : instance;

    if (function is FSRegisterArrayAccessFunction) {
      FSRegisterArrayAccessFunction f = function;
      return reflect(instance).invoke(MirrorSystem.getSymbol("[]"), f.key).reflectee;
    } else {
      FSRegisterNamedFunction f = function;
      var s = _getterSetterSymbol(f);
      if(s != null){
        if(f.name.startsWith('get')){
          return reflect(instance).getField(s).reflectee;
        }
        return reflect(instance).setField(s, f.positionalArguments[0]).reflectee;

      }
      return reflect(instance).invoke( MirrorSystem.getSymbol(f.name), f.positionalArguments, f.namedArguments).reflectee;
    }
  }


}

class SimpleFSRegisterHandler extends FSRegisterHandler {
  final String type;
  final Map<String, Function> functions = new Map<String, Function>();
  Function arrayAccessFunction;

  SimpleFSRegisterHandler(FSRegister register, this.type, [instance = null]) : super(register, instance);

  bool canRunFunction(String type, FSRegisterFunction function, [instance]) {
    if (type != this.type) {
      return false;
    }

    if (function is FSRegisterNamedFunction) {
      FSRegisterNamedFunction f = function;
      return functions.containsKey(f.name);
    }

    if (function is FSRegisterArrayAccessFunction) {
      return arrayAccessFunction != null;
    }
    return false;
  }

  dynamic runFunction(String type, FSRegisterFunction function, [instance]) {

    instance = instance == null ? this.instance : instance;

    if (function is FSRegisterNamedFunction) {
      FSRegisterNamedFunction f = function;
      var posArgs = f.positionalArguments;
      posArgs.insert(0, instance);
      return Function.apply(functions[f.name], posArgs, f.namedArguments);
    } else {
      FSRegisterArrayAccessFunction f = function;
      return arrayAccessFunction(instance, f.key);
    }
  }


}

class FSRegister {

  final List<FSRegisterHandler> handlers = new List<FSRegisterHandler>();

  TypeFSRegisterHandler addType(Type t, [Object instance= null]) {

    var h = new TypeFSRegisterHandler(this, t, instance);
    handlers.add(h);
    return h;
  }

  SimpleFSRegisterHandler add(String type, [Object instance= null]) {
    var h = new SimpleFSRegisterHandler(this, type, instance);
    handlers.add(h);
    return h;
  }


  dynamic _runRegisterFunction(List<String> types, FSRegisterFunction function, [instance = null]) {
    var f = handlers.fold(null, (prev, FSRegisterHandler handler) {
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

  dynamic _runFunction(List<String> types, FSFunction function, [instance = null]) {
    if (function is FSNamedFunction) {
      return _runRegisterFunction(types, new FSRegisterNamedFunction.from(function, runProgram), instance);
    } else {
      return _runRegisterFunction(types, new FSRegisterArrayAccessFunction.from(function), instance);
    }
  }

  dynamic _runFunctionCall(FSFunctionCall f, [Object instance = null]) {
    if (f.target is FSType) {
      FSType t = f.target;
      return _runFunction([t.value], f.function, instance);
    }

    if (f.target is FSFunctionCall) {
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


  dynamic runFunctionString(String s) => runProgram(parseFS(s));

  dynamic runProgram(FSProgram program) {
    if (program == null) {
      return null;
    }
    var calls = program.toFunctionCalls();
    var lastReturn = null;
    calls.forEach((FSFunctionCall c) {
      lastReturn = _runFunctionCall(c);
    });

    return lastReturn;
  }


}

class AAA {

}

class AA implements AAA {

}

class A extends AA {

  int i = 0;

  void inc() {
    i++;
  }

  int get ii => i;
  set ii(int i){
    this.i = i;
  }

  String method(String s) => "TIME: $s";

  operator [] (String s) => null;

  String get l => "";

  set l(String s) {


  }

  String s = "";

}


void main() {

  var register = new FSRegister();

  var rh1 = register.addType(A, new A());
  var rh2 = register.add("CONSTANTS");

  rh2.arrayAccessFunction = (_, String s) => "$s: ${new DateTime.now().toString()}";
  print(parseFS("A..inc()..inc()..getIi()"));
  print(register.runFunctionString("A..inc()..inc()..inc()..getIi(5)"));


}