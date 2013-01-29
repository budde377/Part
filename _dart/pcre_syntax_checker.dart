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

bool checkPCRE(String exp) => checkDelimiters(exp);

bool checkDelimiters(String exp) {
  var re = new RegExp(r'^([^a-zA-Z\\ \s])(.*[^\\])([^a-zA-Z\\ \s])([imsxeADSUXJu])*$');
  if(!re.hasMatch(exp)){
    return false;
  }
  var match = re.allMatches(exp)[0];
  var notEqual;
  if((notEqual = match.group(1) != match.group(3)) && (match.group(1) != "{" || match.group(3) != "}")){
    return false;
  }

  return (notEqual || delimiterFreeContent(exp,exp[0])) && checkRE(match.group(2));
}

bool delimiterFreeContent(String exp, String delimiter) => new RegExp('[^\]$delimiter', caseSensitive:false).allMatches(exp).length == 1;

bool checkRE(String exp) => checkSimpleRE(exp) || checkUnion(exp);

bool checkUnion(String exp) {
  var subStrings = exp.split("|");
  if (subStrings.length <= 1) {
    return false;
  }
  bool success = false;
  for (var i = subStrings.length;i > 0;i--) {
    var start = i - 1;
    var endList = subStrings.getRange(start, subStrings.length - start);
    var startList = subStrings.getRange(0, start);
    success = success || (checkSimpleRE(endList.join("|")) && checkRE(startList.join("|")));
  }
  return success;

}

bool checkSimpleRE(String exp) => checkBasicRE(exp) || checkConcatenation(exp);

bool checkConcatenation(String exp) {
  if (exp.length < 2) {
    return false;
  }
  bool success = false;
  for (var i = 1;i < exp.length;i++) {
    success = success || (checkBasicRE(exp.substring(exp.length - i)) && checkSimpleRE(exp.substring(0, exp.length - i)));
  }
  return success;
}

bool checkBasicRE(String exp) => checkStar(exp) || checkPlus(exp) || checkOneOrMore(exp) || checkElementaryRE(exp);

bool checkStar(String exp) => exp.endsWith("*") && checkElementaryRE(exp.substring(0, exp.length - 1));

bool checkPlus(String exp) => exp.endsWith("+") && checkElementaryRE(exp.substring(0, exp.length - 1));

bool checkOneOrMore(String exp) => exp.endsWith("?") && checkElementaryRE(exp.substring(0, exp.length - 1));

bool checkElementaryRE(String exp) => checkGroup(exp) || checkAny(exp) || checkEOS(exp) || checkChar(exp) || checkSet(exp) || checkSOS(exp) || checkInternalOptionSetting(exp) || checkNonCapturedGroup(exp);

bool checkInternalOptionSetting(String exp) => new RegExp(r'^\(\?[imsxj]+-?[imsxj]*\)$',caseSensitive:false).hasMatch(exp);

bool checkChar(String exp) => (exp.length == 1 && !metaChar.contains(exp)) || (exp.length == 2 && exp.startsWith('\\'));

bool checkGroup(String exp) => exp.startsWith("(") && exp.endsWith(")") && checkRE(exp.substring(1, exp.length - 1));

bool checkNonCapturedGroup(String exp) => exp.startsWith("(?:") && exp.endsWith(")") && checkRE(exp.substring(3, exp.length - 1));

bool checkCapturingSameNumberGroup(String exp) => exp.startsWith("(?|") && exp.endsWith(")") && checkRE(exp.substring(3, exp.length - 1));

bool checkAny(String exp) => exp == ".";

bool checkEOS(String exp) => exp == '\$';

bool checkSOS(String exp) => exp == "^";

bool checkSet(String exp) => checkPositiveSet(exp) || checkNegativeSet(exp);

bool checkPositiveSet(String exp) => exp.startsWith("[") && exp.endsWith("]") && checkSetItems(exp.substring(1, exp.length - 1));

bool checkNegativeSet(String exp) => exp.startsWith("[^") && exp.endsWith("]") && checkSetItems(exp.substring(1, exp.length - 1));

bool checkSetItems(String exp) {
  var success = false;
  success = checkSetItem(exp);
  if (exp.length < 2) {
    return success;
  }
  for (var i = 1;i < exp.length;i++) {
    success = success || (checkSetItems(exp.substring(0, i)) && checkSetItem(exp.substring(i)));
  }
  return success;
}

bool checkSetItem(String exp) => checkChar(exp) || checkRange(exp);

bool checkRange(String exp) =>
(exp.length == 5 && checkChar(exp.substring(0, 2)) && exp[2] == "-" && checkChar(exp.substring(3, 2))) ||
(exp.length == 3 && checkChar(exp[0]) && exp[1] == "-" && checkChar(exp[2])) ||
(exp.length == 4 && ((checkChar(exp.substring(0, 1)) && exp[1] == "-" && checkChar(exp.substring(2))) || (checkChar(exp.substring(0, 2)) && exp[2] == "-" && checkChar(exp.substring(3)))));


main() {
  var exp = r"asd(?:i*)ddd";
  print(checkRE(exp));
}