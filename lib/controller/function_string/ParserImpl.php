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
        return self::concatCallable(function (Target $t, CompositeFunction $f) {
            return new CompositeFunctionCallImpl($t, $f);
        }, $tokens, 'self::parseTarget', 'self::parseCompositeFunction');
    }

    /**
     * @param array $tokens
     * @return CompositeFunction
     */
    private static function parseCompositeFunction(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseChainCompositeFunction', 'self::parseCompositeChainCompositeFunction');
    }

    /**
     * @param array $tokens
     * @return ChainCompositeFunctionImpl
     */
    private static function parseChainCompositeFunction(array $tokens)
    {
        return self::concatCallable(function (FunctionChain $c) {
            return new ChainCompositeFunctionImpl($c);
        }, $tokens, self::generateTokenTester(Lexer::T_DOT), 'self::parseFunctionChain');
    }

    /**
     * @param array $tokens
     * @return CompositeChainCompositeFunctionImpl
     */
    private static function parseCompositeChainCompositeFunction(array $tokens)
    {
        return self::concatCallable(function (CompositeFunction $f, FunctionChain $fc) {
            return new CompositeChainCompositeFunctionImpl($f, $fc);
        }, $tokens, 'self::parseCompositeFunction', self::generateTokenTester(Lexer::T_DOT), 'self::parseFunctionChain');
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

        return self::orCallable($tokens, 'self::parseFunctionChains', 'self::parseFunction');

    }

    /**
     * @param array $tokens
     * @return FunctionChainsImpl
     */
    private static function parseFunctionChains(array $tokens)
    {
        return self::concatCallable(function (FunctionChain $fc, FFunction $f) {
            return new FunctionChainsImpl($fc, $f);
        }, $tokens, 'self::parseFunctionChain', 'self::parseFunction');
    }


    /**
     * @param array $tokens
     * @return FunctionCallImpl
     */
    private static function parseFunctionCall(array $tokens)
    {
        return self::concatCallable(function (Target $t, FFunction $f) {
            return new FunctionCallImpl($t, $f);
        }, $tokens, 'self::parseTarget', 'self::parseFunction');

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
        return self::orCallable($tokens, 'self::parseNoArgumentNamedFunction', 'self::parseArgumentNamedFunction');
    }

    /**
     * @param array $tokens
     * @return NoArgumentNamedFunctionImpl
     */
    private static function parseNoArgumentNamedFunction(array $tokens)
    {
        return self::concatCallable(function (NameImpl $n) {
            return new NoArgumentNamedFunctionImpl($n);
        }, $tokens, self::generateTokenTester(Lexer::T_DOT), 'self::parseName', self::generateTokenTester(Lexer::T_L_PAREN), self::generateTokenTester(Lexer::T_R_PAREN));
    }

    /**
     * @param array $tokens
     * @return ArgumentNamedFunctionImpl
     */
    private static function parseArgumentNamedFunction(array $tokens)
    {
        return self::concatCallable(function (NameImpl $n, ArgumentList $argList) {
            return new ArgumentNamedFunctionImpl($n, $argList);
        }, $tokens, self::generateTokenTester(Lexer::T_DOT), 'self::parseName', self::generateTokenTester(Lexer::T_L_PAREN), 'self::parseArgumentList', self::generateTokenTester(Lexer::T_R_PAREN));
    }

    /**
     * @param array $tokens
     * @return ArrayAccessFunctionImpl
     */
    private static function parseArrayAccessFunction(array $tokens)
    {
        return self::concatCallable(function (ScalarArrayProgram $sap) {
            return new ArrayAccessFunctionImpl($sap);
        }, $tokens, self::generateTokenTester(Lexer::T_L_BRACKET), 'self::parseScalarArrayProgram', self::generateTokenTester(Lexer::T_L_PAREN), 'self::parseArgumentList', self::generateTokenTester(Lexer::T_R_BRACKET));

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
        return self::concatCallable(function (Type $type, NameImpl $name) {
            return new TypeNameImpl($type, $name);
        }, $tokens, 'self::parseType', self::generateTokenTester(Lexer::T_BACKSLASH), 'self::parseName');
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
        return self::concatCallable(function (ScalarArrayProgram $sap, ArgumentList $l) {
            return new ArgumentsImpl($sap, $l);
        }, $tokens, 'self::parseScalarArrayProgram', self::generateTokenTester(Lexer::T_COMMA), 'self::parseArgumentList');
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
        return self::concatCallable(function (NamedArgumentImpl $a, NamedArgumentList $l) {
            return new NamedArgumentsImpl($a, $l);
        }, $tokens, 'self::parseNamedArgument', self::generateTokenTester(Lexer::T_COMMA), 'self::parseNamedArgumentList');

    }


    /**
     * @param array $tokens
     * @return NamedArgumentImpl
     */
    private static function parseNamedArgument(array $tokens)
    {
        return self::concatCallable(function (NameNotStartingWithUnderscoreImpl $n, ScalarArrayProgram $sap) {
            return new NamedArgumentImpl($n, $sap);
        }, $tokens, 'self::parseNameNotStartingWithUnderscore', self::generateTokenTester(Lexer::T_COMMA), 'self::parseScalarArrayProgram');
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
        return self::concatCallable(function (AllArrayEntries $e) {
                return new ArrayImpl($e);
            }, $tokens,
            self::generateTokenTester(Lexer::T_L_BRACKET),
            'self::parseAllArrayEntries',
            self::generateTokenTester(Lexer::T_R_BRACKET)
        );
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
        return self::concatCallable(function (ScalarArrayProgram $sap, AllArrayEntries $e) {
                return new ArrayEntriesImpl($sap, $e);
            }, $tokens,
            'self::parseScalarArrayProgram',
            self::generateTokenTester(Lexer::T_COMMA),
            'self::parseAllArrayEntries');
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
        return self::concatCallable(function (KeyArrowValueImpl $sap, AllArrayEntries $e) {
                return new NamedArrayEntriesImpl($sap, $e);
            }, $tokens,
            'self::parseKeyArrowValue',
            self::generateTokenTester(Lexer::T_COMMA),
            'self::parseAllArrayEntries');
    }

    /**
     * @param array $tokens
     * @return KeyArrowValueImpl
     */
    private static function parseKeyArrowValue(array $tokens)
    {
        return self::concatCallable(function (Scalar $s, ScalarArrayProgram $sap) {
                return new KeyArrowValueImpl($s, $sap);
            }, $tokens,
            'self::parseScalar',
            self::generateTokenTester(Lexer::T_DOUBLE_ARROW),
            'self::parseScalarArrayProgram');
    }

    /**
     * @param array $tokens
     * @return Scalar
     */
    private static function parseScalar(array $tokens)
    {
        return self::orCallable($tokens, 'self::parseBool', 'self::parseNull', 'self::parseNum', 'self::parseString', 'self::parseUnsignedNum');
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
        return self::concatCallable(function (UnsignedNum $num) use ($tokens) {
            return new NumImpl($tokens[0]['match'] == "-" ? NumImpl::SIGN_MINUS : NumImpl::SIGN_PLUS, $num);
        }, $tokens, self::generateTokenTester(Lexer::T_SIGN), 'self::parseUnsignedNum');
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
    private static function parseString(array $tokens)
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

    /**
     * @param callable $constructor
     * @param array $tokens
     * @param callable $c1,...
     * @return mixed
     */
    private static function concatCallable(callable $constructor, array $tokens, $c1)
    {

        $a = func_get_args();
        array_shift($a);
        $args = call_user_func_array('self::concatCallableHelper', $a);
        if($args == null){
            return null;
        }
        $newArgs = [];
        foreach ($args as $arg) {
            if (!is_object($arg)) {
                continue;
            }
            $newArgs[] = $arg;
        }

        return call_user_func_array($constructor, $newArgs);

    }

    /**
     * @param array $tokens
     * @param callable $c1 ...
     * @return mixed
     */

    private static function concatCallableHelper(array $tokens, $c1)
    {
        $n = func_num_args();

        if (count($tokens) < $n - 1) {
            return null;
        }

        if ($n == 2) {
            return [call_user_func($c1, $tokens)];
        }

        for ($i = 1; $i < count($tokens); $i++) {

            $r = call_user_func($c1, array_slice($tokens, 0, $i));
            if ($r == null) {
                continue;
            }
            $a = array_merge([array_slice($tokens, $i)], array_slice(func_get_args(), 2));
            $rest = call_user_func_array('self::concatCallableHelper', $a);
            if ($rest == null || in_array(null, $rest)) {
                continue;
            }
            return array_merge([$r], $rest);
        }

        return null;
    }

    private static function expect(array $tokens, $token)
    {
        return count($tokens) > 0 && $tokens[0]['token'] == $token;
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

}