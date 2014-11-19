library function_string_parser;

/**
 * <program>                    = <composite_function_call> | <function_call>
 *
 * <composite_function_call>    = <target><composite_function>
 * <composite_function>         = ..<function_chain> | <composite_function>..<function_chain>
 * <function_chain>             = <function_chain>.<function> | <function>
 *
 * <function_call>              = <target>.<function> | <target>\[<scalar>\]
 * <function>                   = <name>(<arg_list>) | <name> ()
 * <target>                     = <function_call> | <type>
 * <type>                       = <name> | [a-zA-Z_][A-Za-z0-9_\]+[a-zA-Z_]
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
_matchFirstNotNull("..", s, (Match m, String s) {
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
FSType _parseFSType(String string) =>
_firstNotNull([
    _parseFSName,
        (String s) => new RegExp(r"^[a-z_][a-z0-9_\]+[a-z_]$", caseSensitive:false).hasMatch(s) ? new FSType(s) : null])(string);

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
 * <composite_function>         = ..<function_chain> | <composite_function>..<function_chain>
 */
FSCompositeFunction _parseFSCompositeFunction(String string) =>
string.substring(0, 2) != ".." ? null : _matchFirstNotNull("..", string, (Match m, String s) {
  var c = _parseFSFunctionChain(s.substring(m.start + 2).trim());
  if (c == null) {
    return null;
  }
  if (m.start == 0) {
    return c;
  }

  var cf = _parseFSCompositeFunction(s.substring(0, m.start).trim());

  if (cf == null) {
    return null;
  }
  return new FSFunctionCompositeFunction(cf, c);

});


/*
 * <function_chain>             = <function_chain>.<function> | <function>
 */

FSFunctionChain _parseFSFunctionChain(String s) {
  var f = _parseFSFunction(s);
  if (f != null) {
    return f;
  }
  return _matchFirstNotNull(".", s, (Match m, s) {
    var f = _parseFSFunction(s.substring(m.start + 1).trim());
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
 * <function_call>              = <target>.<function> | <target>\[<scalar>\]
 */

FSFunctionCall _parseFSFunctionCall(String s) =>
_matchFirstNotNull(new RegExp(r"[\[\.]"), s, (Match m, String s) {
  var t = _parseFSTarget(s.substring(0, m.start).trim());
  if (t == null) {
    return null;
  }

  s = s.substring(m.start).trim();
  var scalar;
  var b = m[0] == "[" && s.endsWith("]");
  if (b && (scalar = _parseFSScalar(s.substring(1, s.length - 1).trim())) != null) {
    return new FSArrayAccessFunctionCall(t, scalar);
  }

  if (b) {
    return null;
  }

  var f = _parseFSFunction(s.substring(1).trim());
  if (f == null) {
    return null;
  }
  return new FSFunctionFunctionCall(t, f);

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
  return new FSNumScalar(sign * n.value);
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
FSNumScalar _parseFSDouble(String s) {
  var m = new RegExp(r"^[0-9]*[\.][0-9]*$").firstMatch(s);
  if (m == null) {
    return null;
  }

  return new FSNumScalar(num.parse(m[0]));
}


/*
 * <exp_double_number>          = ([0-9]+|[0-9]*[\.][0-9]*)[eE][+-]?[0-9]+
 */
FSNumScalar _parseFSExpDouble(String s) {
  var m = new RegExp(r"^([0-9]+|[0-9]*[\.][0-9]*)[eE][+-]?[0-9]+$").firstMatch(s);
  if (m == null) {
    return null;
  }

  return new FSNumScalar(num.parse(m[0]));
}

FSNumScalar _parseBase(String s, RegExp p, int base) {
  var m = p.firstMatch(s);
  if (m == null) {
    return null;
  }

  return new FSNumScalar(int.parse(m[1], radix:base));
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
      s.replaceAllMapped(new RegExp(r"([^\\])\\\\" + key), (Match m) => "${m[1]}$value");
    });
    s.replaceAllMapped(new RegExp(r"([^\\])\\\\([0-7]{1,3})"), (Match m) => "${m[1]}" + new String.fromCharCode(int.parse(m[2], radix:8)));
    s.replaceAllMapped(new RegExp(r"([^\\])\\\\x([0-9A-Fa-f]{1,2})"), (Match m) => "${m[1]}" + new String.fromCharCode(int.parse(m[2], radix:16)));
  }
  s.replaceAllMapped(new RegExp(r"([^\\])\\\\" + divider), (Match m) => "${m[1]}$divider");
  s.replaceAll(r"\\", r"\");
  return new FSStringScalar(s.substring(1, s.length - 1));
}

/*
 * <function>                   = <name>(<arg_list>) | <name> ()
 */
FSFunction _parseFSFunction(String s) {

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
  if(sap != null){
    return new FSArrayEntry(sap);
  }

  return _matchFirstNotNull(",", s, (Match m, String s){
    var sap = _parseFSScalarArrayProgram(s.substring(0,m.start).trim());
    if(sap == null){
      return null;
    }

    var entries = _parseFSAllArrayEntries(s.substring(m.start+1).trim());
    if(entries == null){
      return null;
    }
    return new FSArrayEntries(sap, entries);
  });

}
/*
 * <named_array_entries>        = <array_named_entry> | <array_named_entry>, <all_array_entries>
 */
FSNamedArrayEntry _parseFSNamedArrayEntries(String s){
  var ne = _parseFSNamedArrayEntry(s);
  if(ne != null){
    return ne;
  }

  return _matchFirstNotNull(",", s, (Match m, String s){
    var ne = _parseFSNamedArrayEntry(s.substring(0,m.start).trim());
    if(ne == null){
      return null;
    }

    var entries = _parseFSAllArrayEntries(s.substring(m.start+1).trim());
    if(entries == null){
      return null;
    }
    return new FSNamedArrayEntries(ne.key, ne.value, entries);
  });
}
/*
 * <array_named_entry>          = <scalar> => <sap>
 */
FSNamedArrayEntry _parseFSNamedArrayEntry(String s){
  var first_index = s.indexOf("=>");
  if(first_index < 0){
    return null;
  }

  var scalar = _parseFSScalar(s.substring(0, first_index).trim());

  if(scalar == null){
    return null;
  }

  var sap = _parseFSScalarArrayProgram(s.substring(first_index+2).trim());
  if(sap == null){
    return null;
  }
  return new FSNamedArrayEntry(scalar, sap);

}

abstract class FSTarget {


}


class FSType extends FSTarget {
  final String value;

  FSType(this.value);
}


abstract class FSProgram extends FSScalarArrayProgram {
  final FSTarget target;

  FSProgram(this.target);

}

class FSCompositeFunctionCall extends FSProgram implements FSTarget {
  final FSCompositeFunction function;

  FSCompositeFunctionCall(FSTarget target, this.function) : super(target);

}

abstract class FSFunctionCall extends FSProgram {

  FSFunctionCall(FSTarget target) :super(target);
}

class FSFunctionFunctionCall extends FSFunctionCall {
  final FSFunction function;

  FSFunctionFunctionCall(FSTarget target, this.function) : super(target);

}

class FSArrayAccessFunctionCall extends FSFunctionCall {
  final FSScalar key;

  FSArrayAccessFunctionCall(FSTarget target, this.key) : super(target);

}

/*
 * <composite_function>         = ..<function_chain> | <composite_function>..<function_chain>
 */
abstract class FSCompositeFunction {


}

class FSFunctionCompositeFunction extends FSCompositeFunction {
  final FSFunctionChain function;
  final FSCompositeFunction composite;

  FSFunctionCompositeFunction(this.composite, this.function);
}

/*
 * <function_chain>             = <function_chain>.<function> | <function>
 */
abstract class FSFunctionChain extends FSCompositeFunction {

}

class FSChainFunctionChain extends FSFunctionChain {
  final FSChainFunctionChain chain;
  final FSFunction function;

  FSChainFunctionChain(this.chain, this.function);
}

abstract class FSFunction extends FSFunctionChain {
  final FSName name;

  FSFunction(this.name);

}

class FSNoArgumentFunction extends FSFunction {

  FSNoArgumentFunction(FSName name) : super(name);
}

class FSArgumentFunction extends FSFunction {

  final FSArgument argument;

  FSArgumentFunction(FSName name, this.argument) : super(name);

}


class FSArgument {
  final FSScalarArrayProgram value;

  FSArgument(this.value);


}

class FSArguments extends FSArgument {

  final FSArgument argument;

  FSArguments(FSScalarArrayProgram value, this.argument) : super(value);


}

class FSNamedArgument extends FSArgument {
  final FSNotStartingWithUnderscoreName name;

  FSNamedArgument(this.name, FSScalarArrayProgram value) : super(value);
}

class FSNamedArguments extends FSNamedArgument implements FSArguments {
  final FSNamedArgument argument;

  FSNamedArguments(FSNotStartingWithUnderscoreName name, FSScalarArrayProgram value, this.argument) : super(name, value);

}


class FSName {
  final String value;

  FSName(this.value);

}

class FSNotStartingWithUnderscoreName {
  final String value;

  FSNotStartingWithUnderscoreName(this.value) ;

}


abstract class FSScalarArrayProgram {

}

class FSArray extends FSScalarArrayProgram {
  final FSArrayEntry entry;

  FSArray(this.entry);

}

class FSArrayEntry {
  final FSScalarArrayProgram value;

  FSArrayEntry(this.value);

}

class FSArrayEntries extends FSArrayEntry {

  FSArrayEntry entry;

  FSArrayEntries(FSScalarArrayProgram value, this.entry): super(value);
}


class FSNamedArrayEntry extends FSArrayEntry {
  final FSScalar key;

  FSNamedArrayEntry(this.key, FSScalarArrayProgram value) : super(value);

}

class FSNamedArrayEntries extends FSNamedArrayEntry implements FSArrayEntries {
  FSArrayEntry entry;

  FSNamedArrayEntries(FSScalar key, FSScalarArrayProgram value, this.entry) : super(key, value);
}

class FSScalar extends FSScalarArrayProgram {

}


class FSBoolScalar extends FSScalar {
  final bool value;

  FSBoolScalar(this.value);

}

class FSNullScalar extends FSScalar {

}

class FSNumScalar extends FSScalar {
  final num value;

  FSNumScalar(this.value);

}

class FSStringScalar extends FSScalar {
  final String value;

  FSStringScalar(this.value);

}