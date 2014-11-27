<?php
namespace ChristianBudde\cbweb\controller\function_string;

use ChristianBudde\cbweb\controller\function_string\ast\AllArrayEntries;
use ChristianBudde\cbweb\controller\function_string\ast\ArgumentList;
use ChristianBudde\cbweb\controller\function_string\ast\ArgumentNamedFunction;
use ChristianBudde\cbweb\controller\function_string\ast\ArgumentNamedFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ArgumentsImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ArrayAccessFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ArrayEntriesImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ArrayEntry;
use ChristianBudde\cbweb\controller\function_string\ast\ArrayImpl;
use ChristianBudde\cbweb\controller\function_string\ast\BinaryImpl;
use ChristianBudde\cbweb\controller\function_string\ast\BoolImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ChainCompositeFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\CompositeChainCompositeFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\CompositeFunction;
use ChristianBudde\cbweb\controller\function_string\ast\CompositeFunctionCallImpl;
use ChristianBudde\cbweb\controller\function_string\ast\DecimalImpl;
use ChristianBudde\cbweb\controller\function_string\ast\DoubleNumberImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ExpDoubleNumberImpl;
use ChristianBudde\cbweb\controller\function_string\ast\FFunction;
use ChristianBudde\cbweb\controller\function_string\ast\FunctionCallImpl;
use ChristianBudde\cbweb\controller\function_string\ast\FunctionChain;
use ChristianBudde\cbweb\controller\function_string\ast\FunctionChainsImpl;
use ChristianBudde\cbweb\controller\function_string\ast\HexadecimalImpl;
use ChristianBudde\cbweb\controller\function_string\ast\KeyArrowValueImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NamedArgumentImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NamedArgumentList;
use ChristianBudde\cbweb\controller\function_string\ast\NamedArgumentsImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NamedArrayEntriesImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NamedArrayEntry;
use ChristianBudde\cbweb\controller\function_string\ast\NameImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NameNotStartingWithUnderscoreImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NoArgumentNamedFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NullImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NumImpl;
use ChristianBudde\cbweb\controller\function_string\ast\OctalImpl;
use ChristianBudde\cbweb\controller\function_string\ast\Program;
use ChristianBudde\cbweb\controller\function_string\ast\Scalar;
use ChristianBudde\cbweb\controller\function_string\ast\ScalarArrayProgram;
use ChristianBudde\cbweb\controller\function_string\ast\StringImpl;
use ChristianBudde\cbweb\controller\function_string\ast\Target;
use ChristianBudde\cbweb\controller\function_string\ast\Type;
use ChristianBudde\cbweb\controller\function_string\ast\TypeNameImpl;
use ChristianBudde\cbweb\controller\function_string\ast\UnsignedNum;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 5:08 PM
 */
class ParserImpl implements Parser
{




    /**
     * @param array $tokens An assoc. array containing *match* and *token*
     * @return Program
     */
    public static function parse(array $tokens)
    {
        return self::parseProgram(self::clearWhitespace($tokens));
    }


    /**
     * @param array $tokens
     * @return array
     */
    private static function clearWhitespace(array $tokens){
        $result = [];
        foreach ($tokens as $token) {
            if($token['token'] == Lexer::T_WHITESPACE){
                continue;
            }
            $result[] = $token;
        }

        return $result;

    }

    /**
     * @param array
     * @return Program
     */
    private static function parseProgram(array $tokens)
    {

        return self::orCallable($tokens, 'self::parseCompositeFunctionCall', 'self::parseFunctionCall');
    }

    /**
     * @param array $tokens
     * @return CompositeFunctionCallImpl
     */
    private static function parseCompositeFunctionCall(array $tokens)
    {
        if(!self::expect($tokens, [Lexer::T_NAME_NOT_STARTING_WITH_UNDERSCORE, Lexer::T_NAME])){
            return null;
        }

        return self::runThrough(Lexer::T_DOT, $tokens, function (Target $t,CompositeFunction $f){
         return new CompositeFunctionCallImpl($t, $f);
        }, function($i, array $a){
            return self::parseTarget(array_slice($a, 0, $i));
        }, function($i, array $a){
            return self::parseCompositeFunction(array_slice($a, $i));
        });


    }

    /**
     * @param array $tokens
     * @return CompositeFunction
     */
    private static function parseCompositeFunction(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseCompositeChainCompositeFunction', 'self::parseChainCompositeFunction');
    }

    /**
     * @param array $tokens
     * @return ChainCompositeFunctionImpl
     */
    private static function parseChainCompositeFunction(array $tokens)
    {
        if(!self::expect($tokens, Lexer::T_DOT) || !self::expect($tokens, [Lexer::T_DOT, Lexer::T_L_BRACKET], 1)){
            return null;
        }

        return ($p = self::parseFunctionChain(array_slice($tokens, 1)))?new ChainCompositeFunctionImpl($p):null;

    }

    /**
     * @param array $tokens
     * @return CompositeChainCompositeFunctionImpl
     */
    private static function parseCompositeChainCompositeFunction(array $tokens)
    {
        if(!self::expect($tokens, Lexer::T_DOT) || !self::expect($tokens, [Lexer::T_DOT, Lexer::T_L_BRACKET], 1) || !self::expect($tokens, [Lexer::T_R_BRACKET, Lexer::T_R_PAREN], -1)){
            return null;
        }

        return self::runThrough(Lexer::T_DOT, $tokens, function(CompositeFunction $cf, FunctionChain $fc){
            return new CompositeChainCompositeFunctionImpl($cf, $fc);
        }, function($i, array $tokens){
            return self::parseCompositeFunction(array_slice($tokens,0,$i));
        }, function($i, array $tokens){
            return self::parseFunctionChain(array_slice($tokens, $i+1));
        });

    }

    private static function generateTokenTester($token)
    {
        return function (array $tokens) use ($token) {

            if (count($tokens) != 1) {
                return null;
            }
            if (!self::expect($tokens, $token)) {
                return null;
            }
            return true;
        };

    }


    /**
     * @param array $tokens
     * @return FunctionChain
     */
    private static function parseFunctionChain(array $tokens)
    {

        return self::orCallable($tokens, 'self::parseFunctionChains' , 'self::parseFunction');

    }

    /**
     * @param array $tokens
     * @return FunctionChainsImpl
     */
    private static function parseFunctionChains(array $tokens)
    {
        if(!self::expect($tokens, Lexer::T_DOT)){
            return null;
        }

        return self::runThrough([Lexer::T_DOT, Lexer::T_L_BRACKET], $tokens, function(FunctionChain $fc, FFunction $f){
            return new FunctionChainsImpl($fc, $f);
        }, function($i, array $tokens){
            return self::parseFunctionChain(array_slice($tokens,0,$i));
        }, function($i, array $tokens){
            return self::parseFunction(array_slice($tokens, $i));
        });


    }


    /**
     * @param array $tokens
     * @return FunctionCallImpl
     */
    private static function parseFunctionCall(array $tokens)
    {
        if(!self::expect($tokens, [Lexer::T_NAME_NOT_STARTING_WITH_UNDERSCORE, Lexer::T_NAME])){
            return null;
        }

        return self::runThrough([Lexer::T_DOT, Lexer::T_L_BRACKET], $tokens, function(Target $target, FFunction $f){
            return new FunctionCallImpl($target, $f);
        }, function($i, array $tokens){
            return self::parseTarget(array_slice($tokens,0,$i));
        }, function($i, array $tokens){
            return self::parseFunction(array_slice($tokens, $i));
        });

    }


    /**
     * @param array $tokens
     * @return FFunction
     */
    private static function parseFunction(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseNamedArgumentFunction', 'self::parseArrayAccessFunction');
    }

    /**
     * @param array $tokens
     * @return ArgumentNamedFunction
     */
    private static function parseNamedArgumentFunction(array $tokens)
    {
        if(!self::expect($tokens, Lexer::T_DOT) || !self::expect($tokens, [Lexer::T_NAME_NOT_STARTING_WITH_UNDERSCORE, Lexer::T_NAME], 1) || !self::expect($tokens, Lexer::T_L_PAREN, 2) || !self::expect($tokens, Lexer::T_R_PAREN, -1)){
            return null;
        }

        return self::orCallable($tokens, 'self::parseNoArgumentNamedFunction', 'self::parseArgumentNamedFunction');
    }

    /**
     * @param array $tokens
     * @return NoArgumentNamedFunctionImpl
     */
    private static function parseNoArgumentNamedFunction(array $tokens)
    {
        if(count($tokens) != 4){
            return null;
        }

        return ($n = self::parseName(array_slice($tokens, 1,1))) == null?null: new NoArgumentNamedFunctionImpl($n);


    }

    /**
     * @param array $tokens
     * @return ArgumentNamedFunctionImpl
     */
    private static function parseArgumentNamedFunction(array $tokens)
    {

        if(count($tokens) < 4){
            return null;
        }

        $n =  self::parseName(array_slice($tokens, 1,1));
        if($n == null){
            return null;
        }
        $args = self::parseArgumentList(array_slice($tokens, 3, count($tokens)-4));

        return $args == null?null: new ArgumentNamedFunctionImpl($n, $args);
    }

    /**
     * @param array $tokens
     * @return ArrayAccessFunctionImpl
     */
    private static function parseArrayAccessFunction(array $tokens)
    {

        if(!self::expect($tokens, Lexer::T_L_BRACKET) || !self::expect($tokens, Lexer::T_R_BRACKET, -1)){
            return null;
        }

        return ($arg = self::parseScalarArrayProgram(array_slice($tokens, 1, count($tokens)-2))) == null?null:new ArrayAccessFunctionImpl($arg);
    }


    /**
     * @param array $tokens
     * @return Target
     */
    private static function parseTarget(array $tokens)
    {

        return self::orCallable($tokens, 'self::parseFunctionCall', 'self::parseType');
    }


    /**
     * @param array $tokens
     * @return Type
     */
    private static function parseType(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseName', 'self::parseTypeName');
    }


    /**
     * @param array $tokens
     * @return TypeNameImpl
     */
    private static function parseTypeName(array $tokens)
    {
        if(!self::expect($tokens, [Lexer::T_NAME, Lexer::T_NAME_NOT_STARTING_WITH_UNDERSCORE])){
            return null;
        }

        $i = self::findNext(Lexer::T_BACKSLASH, array_reverse($tokens));
        if($i === false){
            return null;
        }
        $i = count($tokens) - $i;

        if(($name = self::parseName(array_slice($tokens, 0, $i))) == null){
            return null;
        }

        return ($t = self::parseType(array_slice($tokens, $i+1))) == null?null:new TypeNameImpl($t, $name);
    }


    /**
     * @param array $tokens
     * @return ArgumentList
     */
    private static function parseArgumentList(array $tokens)
    {
        return self::orCallable($tokens,
            'self::parseScalarArrayProgram',
            'self::parseArguments',
            'self::parseNamedArgumentList');
    }

    /**
     * @param array $tokens
     * @return ArgumentsImpl
     */
    private static function parseArguments(array $tokens)
    {
        if(!self::containsToken($tokens, Lexer::T_COMMA)){
            return null;
        }

        return self::runThrough(Lexer::T_COMMA, $tokens, function (ScalarArrayProgram $sap, ArgumentList $argList){
            return new ArgumentsImpl($sap, $argList);
        }, function ($i, array $tokens){
            return self::parseScalarArrayProgram(array_slice($tokens, 0, $i));
        }, function ($i, array $tokens){
            return self::parseArgumentList(array_slice($tokens, $i+1));
        });

    }


    /**
     * @param array $tokens
     * @return NamedArgumentList
     */
    private static function parseNamedArgumentList(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseNamedArgument', 'self::parseNamedArguments');
    }

    /**
     * @param array $tokens
     * @return NamedArgumentsImpl
     */
    private static function parseNamedArguments(array $tokens)
    {
        if(!self::containsToken($tokens, Lexer::T_COMMA)){
            return null;
        }

        return self::runThrough(Lexer::T_COMMA, $tokens, function (NamedArgumentImpl $sap, NamedArgumentList $argList){
            return new NamedArgumentsImpl($sap, $argList);
        }, function ($i, array $tokens){
            return self::parseNamedArgument(array_slice($tokens, 0, $i));
        }, function ($i, array $tokens){
            return self::parseNamedArgumentList(array_slice($tokens, $i+1));
        });
    }


    /**
     * @param array $tokens
     * @return NamedArgumentImpl
     */
    private static function parseNamedArgument(array $tokens)
    {
        if(count($tokens) < 3 || !self::expect($tokens, Lexer::T_NAME_NOT_STARTING_WITH_UNDERSCORE) || !self::expect($tokens, Lexer::T_COLON,1)){
            return null;
        }

        if(($n = self::parseNameNotStartingWithUnderscore([$tokens[0]])) == null){
            return null;
        }

        return ($sap = self::parseScalarArrayProgram(array_slice($tokens, 2))) == null?null:new NamedArgumentImpl($n, $sap);
    }

    /**
     * @param array $tokens
     * @return ScalarArrayProgram
     */
    private static function parseScalarArrayProgram(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseScalar', 'self::parseArray', 'self::parseProgram');
    }


    /**
     * @param array $tokens
     * @return ArrayImpl
     */
    private static function parseArray(array $tokens)
    {
        if(!self::expect($tokens, Lexer::T_L_BRACKET) || !self::expect($tokens, Lexer::T_R_BRACKET, -1)){
            return null;
        }

        return ($a = self::parseAllArrayEntries(array_slice($tokens, 1, count($tokens)-2))) == null?null:new ArrayImpl($a);

    }

    /**
     * @param array $tokens
     * @return AllArrayEntries
     */
    private static function parseAllArrayEntries(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseArrayEntry', 'self::parseNamedArrayEntry');
    }

    /**
     * @param array $tokens
     * @return ArrayEntry
     */
    private static function parseArrayEntry(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseScalarArrayProgram', 'self::parseArrayEntries');
    }

    /**
     * @param array $tokens
     * @return ArrayEntriesImpl
     */
    private static function parseArrayEntries(array $tokens)
    {
        if(!self::containsToken($tokens, Lexer::T_COMMA)){
            return null;
        }

        return self::runThrough(Lexer::T_COMMA, $tokens, function (ScalarArrayProgram $sap, AllArrayEntries $argList){
            return new ArrayEntriesImpl($sap, $argList);
        }, function ($i, array $tokens){
            return self::parseScalarArrayProgram(array_slice($tokens, 0, $i));
        }, function ($i, array $tokens){
            return self::parseAllArrayEntries(array_slice($tokens, $i+1));
        });

    }

    /**
     * @param array $tokens
     * @return NamedArrayEntry
     */
    private static function parseNamedArrayEntry(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseKeyArrowValue', 'self::parseNamedArrayEntries');
    }

    /**
     * @param array $tokens
     * @return NamedArrayEntriesImpl
     */
    private static function parseNamedArrayEntries(array $tokens)
    {
        if(!self::containsToken($tokens, Lexer::T_COMMA)){
            return null;
        }

        return self::runThrough(Lexer::T_COMMA, $tokens, function (KeyArrowValueImpl $sap, AllArrayEntries $argList){
            return new NamedArrayEntriesImpl($sap, $argList);
        }, function ($i, array $tokens){
            return self::parseKeyArrowValue(array_slice($tokens, 0, $i));
        }, function ($i, array $tokens){
            return self::parseAllArrayEntries(array_slice($tokens, $i+1));
        });
    }

    /**
     * @param array $tokens
     * @return KeyArrowValueImpl
     */
    private static function parseKeyArrowValue(array $tokens)
    {
        if(count($tokens) < 3 || !self::containsToken($tokens, Lexer::T_DOUBLE_ARROW)){
            return null;
        }

        $i = self::findNext(Lexer::T_DOUBLE_ARROW, $tokens);
        if($i == false){
            return null;
        }

        if(($scalar = self::parseScalar(array_slice($tokens, 0, $i))) == null){
            return null;
        }

        return ($sap = self::parseScalarArrayProgram(array_slice($tokens, $i+1))) == null?null:new KeyArrowValueImpl($scalar, $sap);

    }

    /**
     * @param array $tokens
     * @return Scalar
     */
    private static function parseScalar(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseBool', 'self::parseNull', 'self::parseNum', 'self::parseStringScalar', 'self::parseUnsignedNum');
    }


    /**
     * @param array $tokens
     * @return BoolImpl
     */
    private static function parseBool(array $tokens)
    {
        return self::expectGenerator(function ($t) {
            return new BoolImpl($t);
        }, $tokens, Lexer::T_BOOL);
    }


    /**
     * @param array $tokens
     * @return NullImpl
     */
    private static function parseNull(array $tokens)
    {
        return self::expectGenerator(function () {
            return new NullImpl();
        }, $tokens, Lexer::T_NULL);
    }

    /**
     * @param array $tokens
     * @return NameNotStartingWithUnderscoreImpl
     */
    private static function parseNameNotStartingWithUnderscore(array $tokens)
    {
        return self::expectGenerator(function ($t) {
            return new NameNotStartingWithUnderscoreImpl($t);
        }, $tokens, Lexer::T_NAME_NOT_STARTING_WITH_UNDERSCORE);

    }

    /**
     * @param array $tokens
     * @return NameImpl
     */
    private static function parseName(array $tokens)
    {

        return self::orCallable($tokens, 'self::parseNameNotStartingWithUnderscore', 'self::parseNameStaringWithUnderscore');

    }

    /**
     * @param array $tokens
     * @return NameImpl
     */
    private static function parseNameStaringWithUnderscore(array $tokens)
    {
        return self::expectGenerator(function ($t) {
            return new NameImpl($t);
        }, $tokens, Lexer::T_NAME);

    }

    /**
     * @param array $tokens
     * @return NumImpl
     */
    private static function parseNum(array $tokens)
    {
        if(!self::expect($tokens, Lexer::T_SIGN)){
            return null;
        }

        return ($uNum = self::parseUnsignedNum(array_slice($tokens, 1))) === null?null:new NumImpl($tokens[0]['match'] == '-'?NumImpl::SIGN_MINUS:NumImpl::SIGN_PLUS, $uNum);
    }

    /**
     * @param array $tokens
     * @return UnsignedNum
     */
    private static function parseUnsignedNum(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseInteger', 'self::parseFloat');
    }

    /**
     * @param array $tokens
     * @return Integer
     */
    private static function parseInteger(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseOctal', 'self::parseDecimal', 'self::parseHexadecimal', 'self::parseBinary');
    }

    /**
     * @param array $tokens
     * @return Float
     */
    private static function parseFloat(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseDoubleNumber', 'self::parseExpDoubleNumber');
    }

    /**
     * @param array $tokens
     * @return StringImpl
     */
    private static function parseStringScalar(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseSingleQuotedString', 'self::parseDoubleQuotedString');
    }

    /**
     * @param array $tokens
     * @return StringImpl
     */
    private static function parseSingleQuotedString(array $tokens)
    {
        return self::expectGenerator(function ($match) {
            return new StringImpl(self::transformSingleQuotedString($match));
        }, $tokens, Lexer::T_SINGLE_QUOTED_STRING);
    }

    /**
     * @param array $tokens
     * @return StringImpl
     */
    private static function parseDoubleQuotedString(array $tokens)
    {
        return self::expectGenerator(function ($match) {
            return new StringImpl(self::transformDoubleQuotedString($match));
        }, $tokens, Lexer::T_DOUBLE_QUOTED_STRING);
    }

    /**
     * @param array $tokens
     * @return DecimalImpl
     */
    private static function parseDecimal(array $tokens)
    {
        return self::expectGenerator(function ($match) {
            return new DecimalImpl(intval($match));
        }, $tokens, Lexer::T_DECIMAL);
    }

    /**
     * @param array $tokens
     * @return HexadecimalImpl
     */
    private static function parseHexaDecimal(array $tokens)
    {
        return self::expectGenerator(function ($match) {
            return new HexadecimalImpl(intval($match, 16));
        }, $tokens, Lexer::T_HEXADECIMAL);
    }


    /**
     * @param array $tokens
     * @return OctalImpl
     */
    private static function parseOctal(array $tokens)
    {
        return self::expectGenerator(function ($match) {
            return new OctalImpl(intval($match, 8));
        }, $tokens, Lexer::T_OCTAL);
    }

    /**
     * @param array $tokens
     * @return BinaryImpl
     */
    private static function parseBinary(array $tokens)
    {
        return self::expectGenerator(function ($match) {
            return new BinaryImpl(intval(substr($match, 2), 2));
        }, $tokens, Lexer::T_BINARY);
    }

    /**
     * @param array $tokens
     * @return DoubleNumberImpl
     */
    private static function parseDoubleNumber(array $tokens)
    {
        return self::expectGenerator(function ($match) {
            return new DoubleNumberImpl(floatval($match));
        }, $tokens, Lexer::T_DOUBLE_NUMBER);
    }

    /**
     * @param array $tokens
     * @return ExpDoubleNumberImpl
     */
    private static function parseExpDoubleNumber(array $tokens)
    {
        return self::expectGenerator(function ($match) {
            return new ExpDoubleNumberImpl(floatval($match));
        }, $tokens, Lexer::T_EXP_DOUBLE_NUMBER);
    }

    /**
     * @param array $tokens
     * @param callable $c1,...
     * @return mixed
     */
    private static function orCallable(array $tokens, $c1)
    {

        $p = call_user_func($c1, $tokens);
        if ($p != null) {
            return $p;
        }

        if(func_num_args() <= 2){
            return null;
        }

        $a = func_get_args();
        $t = array_shift($a);
        array_shift($a);
        array_unshift($a, $t);
        return call_user_func_array('self::orCallable', $a);

    }


    private static function expect(array $tokens, $token, $index = 0)
    {

        if(abs($index) >= count($tokens)){
            return false;
        }

        $index = $index < 0?count($tokens)+$index:$index;
        if(is_array($token)){
            return in_array($tokens[$index]['token'], $token);
        }

        return $tokens[$index]['token'] == $token;
    }

    private static function expectGenerator(callable $constructor, array $tokens, $token)
    {
        if (count($tokens) != 1) {
            return null;
        }

        return self::expect($tokens, $token) ? $constructor($tokens[0]['match']) : null;
    }


    private static function transformDoubleQuotedString($input)
    {
        $startCharacter = '"';
        $input = preg_replace("|([^\\\\])\\\\n|", "$1\n", $input);
        $input = preg_replace("/([^\\\\])\\\\r/", "$1\r", $input);
        $input = preg_replace("/([^\\\\])\\\\t/", "$1\t", $input);
        $input = preg_replace("/([^\\\\])\\\\v/", "$1\v", $input);
        $input = preg_replace("/([^\\\\])\\\\e/", "$1\e", $input);
        $input = preg_replace("/([^\\\\])\\\\f/", "$1\f", $input);
        $input = preg_replace_callback("/([^\\\\])\\\\([0-7]{1,3})/",
            function ($m) {
                return $m[1] . chr(octdec($m[2]));
            }, $input);
        $input = preg_replace_callback("/([^\\\\])\\\\x([0-9A-Fa-f]{1,2})/",
            function ($m) {
                return $m[1] . chr(hexdec($m[2]));
            }, $input);


        $input = preg_replace("/([^\\\\])\\\\$startCharacter/", "$1$startCharacter", $input);
        $input = str_replace("\\\\", "\\", $input);
        return substr($input, 1, strlen($input) - 2);

    }

    private static function transformSingleQuotedString($input)
    {

        $startCharacter = "'";
        $result = preg_replace("/([^\\\\])\\\\$startCharacter/", "$1$startCharacter", $input);
        $result = str_replace("\\\\", "\\", $result);
        return substr($result, 1, strlen($result) - 2);

    }


    private static function containsToken(array $tokens, $token){
        foreach($tokens as $t){
            if($t['token'] == $token){
                return true;
            }
        }
        return false;
    }

    /**
     * @param $needle
     * @param array $haystack
     * @param int $from
     * @return int | bool
     */
    private static function findNext($needle, array $haystack, $from= 0){
        foreach (array_slice($haystack, $from, null, true) as $k => $m) {
            if((($a = is_array($needle)) && in_array($m['token'], $needle)) || (!$a && $m['token'] == $needle)){
                return $k;
            }
        }

        return false;
    }

    /**
     * @param $needle
     * @param array $tokens
     * @param callable $constructor
     * @param callable $func,...
     * @return mixed|null
     */
    private static function runThrough($needle, array $tokens, callable $constructor, callable $func){
        $i = self::findNext($needle, $tokens);
        $r = null;
        while($i !== false && $r == null){

            $args = array_slice(func_get_args(), 3);
            foreach ($args as $k=>$f) {
                if(in_array(null, $args)){
                    continue;
                }
                $args[$k] = $f($i, $tokens);
            }
            if(!in_array(null, $args)){
                $r = call_user_func_array($constructor, $args);
            }

            $i = self::findNext($needle, $tokens, $i+1);
        }

        return $r;

    }

    /**
     * @param string $input
     * @return Program
     */
    public static function parseString($input)
    {
        return self::parse(LexerImpl::lex($input));
    }
}