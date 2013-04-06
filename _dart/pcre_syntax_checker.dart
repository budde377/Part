library pcre_syntax_checker;

/*
<RE>	          ::=   <union> | <simple-RE>
<union>	        ::=   <RE> "|" <simple-RE>
<simple-RE>	    ::=   <concatenation> | <basic-RE>
<concatenation> ::=   <simple-RE> <basic-RE>
<basic-RE>	    ::=   <star> | <plus> | <one-or-more> | <elementary-RE>
<star>	        ::=	  <elementary-RE> "*"
<plus>	        ::=	  <elementary-RE> "+"
<one-or-more>   ::=   <elementary-RE> "?"
<elementary-RE>	::=   <group> | <any> | <eos> | <char> | <set>
<group>	        ::=	  "(" <RE> ")"
<any>	          ::=	  "."
<eos>	          ::=	  "$"
<char>	        ::=	  any non metacharacter | "\" metacharacter
<set>	          ::=	  <positive-set> | <negative-set>
<positive-set>	::=	  "[" <set-items> "]"
<negative-set>	::=	  "[^" <set-items> "]"
<set-items>	    ::=	  <set-item> | <set-item> <set-items>
<set-item>	    ::=	  <range> | <char>
<range>	        ::=	  <char> "-" <char>

 */

var metaChar = ['\\', '^', r'$', '.', '[', ']', '|', '(', ')', '?', '*', '+', '}', '{', '-' ];

bool checkPCRE(String exp) => _checkDelimiters(exp);

bool _checkDelimiters(String exp) {
  var re = new RegExp(r'^([^a-zA-Z\\ \s])(.*[^\\])([^a-zA-Z\\ \s])([imsxeADSUXJu])*$');
  if(!re.hasMatch(exp)){
    return false;
  }
  var match = (re.allMatches(exp))[0];
  var notEqual;
  if((notEqual = match.group(1) != match.group(3)) && (match.group(1) != "{" || match.group(3) != "}")){
    return false;
  }

  return (notEqual || _delimiterFreeContent(exp,exp[0])) && _checkRE(match.group(2));
}

bool _delimiterFreeContent(String exp, String delimiter) => new RegExp('[^\]$delimiter', caseSensitive:false).allMatches(exp).length == 1;

bool _checkRE(String exp) => _checkSimpleRE(exp) || _checkUnion(exp);

bool _checkUnion(String exp) {
  var subStrings = exp.split("|");
  if (subStrings.length <= 1) {
    return false;
  }
  bool success = false;
  for (var i = subStrings.length;i > 0;i--) {
    var start = i - 1;
    var endList = subStrings.getRange(start, subStrings.length - start);
    var startList = subStrings.getRange(0, start);
    success = success || (_checkSimpleRE(endList.join("|")) && _checkRE(startList.join("|")));
  }
  return success;

}

bool _checkSimpleRE(String exp) => _checkBasicRE(exp) || checkConcatenation(exp);

bool checkConcatenation(String exp) {
  if (exp.length < 2) {
    return false;
  }
  bool success = false;
  for (var i = 1;i < exp.length;i++) {
    success = success || (_checkBasicRE(exp.substring(exp.length - i)) && _checkSimpleRE(exp.substring(0, exp.length - i)));
  }
  return success;
}

bool _checkBasicRE(String exp) => _checkStar(exp) || _checkPlus(exp) || _checkOneOrMore(exp) || _checkElementaryRE(exp);

bool _checkStar(String exp) => exp.endsWith("*") && _checkElementaryRE(exp.substring(0, exp.length - 1));

bool _checkPlus(String exp) => exp.endsWith("+") && _checkElementaryRE(exp.substring(0, exp.length - 1));

bool _checkOneOrMore(String exp) => exp.endsWith("?") && _checkElementaryRE(exp.substring(0, exp.length - 1));

bool _checkElementaryRE(String exp) => _checkGroup(exp) || _checkAny(exp) || _checkEOS(exp) || _checkChar(exp) || _checkSet(exp) || _checkSOS(exp) || _checkInternalOptionSetting(exp) || _checkNonCapturedGroup(exp);

bool _checkInternalOptionSetting(String exp) => new RegExp(r'^\(\?[imsxj]+-?[imsxj]*\)$',caseSensitive:false).hasMatch(exp);

bool _checkChar(String exp) => (exp.length == 1 && !metaChar.contains(exp)) || (exp.length == 2 && exp.startsWith('\\'));

bool _checkGroup(String exp) => exp.startsWith("(") && exp.endsWith(")") && _checkRE(exp.substring(1, exp.length - 1));

bool _checkNonCapturedGroup(String exp) => exp.startsWith("(?:") && exp.endsWith(")") && _checkRE(exp.substring(3, exp.length - 1));

bool _checkCapturingSameNumberGroup(String exp) => exp.startsWith("(?|") && exp.endsWith(")") && _checkRE(exp.substring(3, exp.length - 1));

bool _checkAny(String exp) => exp == ".";

bool _checkEOS(String exp) => exp == '\$';

bool _checkSOS(String exp) => exp == "^";

bool _checkSet(String exp) => _checkPositiveSet(exp) || _checkNegativeSet(exp);

bool _checkPositiveSet(String exp) => exp.startsWith("[") && exp.endsWith("]") && _checkSetItems(exp.substring(1, exp.length - 1));

bool _checkNegativeSet(String exp) => exp.startsWith("[^") && exp.endsWith("]") && _checkSetItems(exp.substring(1, exp.length - 1));

bool _checkSetItems(String exp) {
  var success = false;
  success = _checkSetItem(exp);
  if (exp.length < 2) {
    return success;
  }
  for (var i = 1;i < exp.length;i++) {
    success = success || (_checkSetItems(exp.substring(0, i)) && _checkSetItem(exp.substring(i)));
  }
  return success;
}

bool _checkSetItem(String exp) => _checkChar(exp) || _checkRange(exp);

bool _checkRange(String exp) =>
(exp.length == 5 && _checkChar(exp.substring(0, 2)) && exp[2] == "-" && _checkChar(exp.substring(3, 2))) ||
(exp.length == 3 && _checkChar(exp[0]) && exp[1] == "-" && _checkChar(exp[2])) ||
(exp.length == 4 && ((_checkChar(exp.substring(0, 1)) && exp[1] == "-" && _checkChar(exp.substring(2))) || (_checkChar(exp.substring(0, 2)) && exp[2] == "-" && _checkChar(exp.substring(3)))));


main() {
  var exp = r"asd(?:i*)ddd";
  print(_checkRE(exp));
}