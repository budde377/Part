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
        return self::parseProgram($tokens);
    }

    /**
     * @param array
     * @return Program
     */
    private static function parseProgram(array $tokens)
    {
        return self::orCallable($tokens, 'parseCompositeFunctionCall', 'parseFunctionCall');
    }

    /**
     * @param array $tokens
     * @return CompositeFunctionCallImpl
     */
    private static function parseCompositeFunctionCall(array $tokens)
    {
        return self::concatCallable(function (Target $t, CompositeFunction $f) {
            return new CompositeFunctionCallImpl($t, $f);
        }, $tokens, 'parseTarget', 'parseCompositeFunction');
    }

    /**
     * @param array $tokens
     * @return CompositeFunction
     */
    private static function parseCompositeFunction(array $tokens)
    {
        return self::orCallable($tokens, 'parseChainCompositeFunction', 'parseCompositeChainCompositeFunction');
    }

    /**
     * @param array $tokens
     * @return ChainCompositeFunctionImpl
     */
    private static function parseChainCompositeFunction(array $tokens)
    {
        if (!self::expect($tokens, Lexer::T_DOT)) {
            return null;
        }
        return self::parseFunctionChain(array_splice($tokens, 1));
    }

    /**
     * @param array $tokens
     * @return CompositeChainCompositeFunctionImpl
     */
    private static function parseCompositeChainCompositeFunction(array $tokens)
    {
        return self::concatCallable(function (CompositeFunction $f, FunctionChain $fc) {
            return new CompositeChainCompositeFunctionImpl($f, $fc);
        }, $tokens, 'parseCompositeFunction', self::generateTokenTester(Lexer::T_DOT), 'parseFunctionChain');
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

        return self::orCallable($tokens, 'parseFunctionChains', 'parseFunction');

    }

    /**
     * @param array $tokens
     * @return FunctionChainsImpl
     */
    private static function parseFunctionChains(array $tokens)
    {
        return self::concatCallable(function (FunctionChain $fc, FFunction $f) {
            return new FunctionChainsImpl($fc, $f);
        }, $tokens, 'parseFunctionChain', 'parseFunction');
    }


    /**
     * @param array $tokens
     * @return FunctionCallImpl
     */
    private static function parseFunctionCall(array $tokens)
    {
        return self::concatCallable(function (Target $t, FFunction $f) {
            return new FunctionCallImpl($t, $f);
        }, $tokens, 'parseTarget', 'parseFunction');

    }


    /**
     * @param array $tokens
     * @return FFunction
     */
    private static function parseFunction(array $tokens)
    {

        return self::orCallable($tokens, 'parseNamedArgumentFunction', 'parseArrayAccessFunction');
    }

    /**
     * @param array $tokens
     * @return ArgumentNamedFunction
     */
    private static function parseNamedArgumentFunction(array $tokens)
    {
        return self::orCallable($tokens, 'parseNoArgumentNamedFunction', 'parseArgumentNamedFunction');
    }

    /**
     * @param array $tokens
     * @return NoArgumentNamedFunctionImpl
     */
    private static function parseNoArgumentNamedFunction(array $tokens)
    {
        return self::concatCallable(function (NameImpl $n) {
            return new NoArgumentNamedFunctionImpl($n);
        }, $tokens, self::generateTokenTester(Lexer::T_DOT), 'parseName', self::generateTokenTester(Lexer::T_L_PAREN), self::generateTokenTester(Lexer::T_R_PAREN));
    }

    /**
     * @param array $tokens
     * @return ArgumentNamedFunctionImpl
     */
    private static function parseArgumentNamedFunction(array $tokens)
    {
        return self::concatCallable(function (NameImpl $n, ArgumentList $argList) {
            return new ArgumentNamedFunctionImpl($n, $argList);
        }, $tokens, self::generateTokenTester(Lexer::T_DOT), 'parseName', self::generateTokenTester(Lexer::T_L_PAREN), 'parseArgumentList', self::generateTokenTester(Lexer::T_R_PAREN));
    }

    /**
     * @param array $tokens
     * @return ArrayAccessFunctionImpl
     */
    private static function parseArrayAccessFunction(array $tokens)
    {
        return self::concatCallable(function (ScalarArrayProgram $sap) {
            return new ArrayAccessFunctionImpl($sap);
        }, $tokens, self::generateTokenTester(Lexer::T_L_BRACKET), 'parseScalarArrayProgram', self::generateTokenTester(Lexer::T_L_PAREN), 'parseArgumentList', self::generateTokenTester(Lexer::T_R_BRACKET));

    }


    /**
     * @param array $tokens
     * @return Target
     */
    private static function parseTarget(array $tokens)
    {

    }


    /**
     * @param array $tokens
     * @return Type
     */
    private static function parseType(array $tokens)
    {

    }


    /**
     * @param array $tokens
     * @return TypeNameImpl
     */
    private static function parseTypeNameImpl(array $tokens)
    {

    }


    /**
     * @param array $tokens
     * @return ArgumentList
     */
    private static function parseArgumentList(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return ArgumentsImpl
     */
    private static function parseArguments(array $tokens)
    {

    }


    /**
     * @param array $tokens
     * @return NamedArgumentList
     */
    private static function parseNamedArgumentList(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return NamedArgumentsImpl
     */
    private static function parseNamedArguments(array $tokens)
    {

    }


    /**
     * @param array $tokens
     * @return NamedArgumentImpl
     */
    private static function parseNamedArgument(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return ScalarArrayProgram
     */
    private static function parseScalarArrayProgram(array $tokens)
    {

    }


    /**
     * @param array $tokens
     * @return ArrayImpl
     */
    private static function parseArray(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return AllArrayEntries
     */
    private static function parseAllArrayEntries(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return ArrayEntry
     */
    private static function parseArrayEntry(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return ArrayEntriesImpl
     */
    private static function parseArrayEntries(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return NamedArrayEntry
     */
    private static function parseNamedArrayEntry(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return NamedArrayEntriesImpl
     */
    private static function parseNamedArrayEntries(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return KeyArrowValueImpl
     */
    private static function parseKeyArrowValue(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return Scalar
     */
    private static function parseScalar(array $tokens)
    {

    }


    /**
     * @param array $tokens
     * @return BoolImpl
     */
    private static function parseBool(array $tokens)
    {

    }


    /**
     * @param array $tokens
     * @return NullImpl
     */
    private static function parseNull(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return NameNotStartingWithUnderscoreImpl
     */
    private static function parseNameNotStartingWithUnderscore(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return NameImpl
     */
    private static function parseName(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return NumImpl
     */
    private static function parseNum(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return UnsignedNum
     */
    private static function parseUnsignedNum(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return Integer
     */
    private static function parseInteger(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return Float
     */
    private static function parseFloat(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return StringImpl
     */
    private static function parseString(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return DecimalImpl
     */
    private static function parseDecimal(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return HexadecimalImpl
     */
    private static function parseHexaDecimal(array $tokens)
    {

    }


    /**
     * @param array $tokens
     * @return OctalImpl
     */
    private static function parseOctal(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return BinaryImpl
     */
    private static function parseBinary(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return DoubleNumberImpl
     */
    private static function parseDoubleNumber(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @return ExpDoubleNumberImpl
     */
    private static function parseExpDoubleNumber(array $tokens)
    {

    }

    /**
     * @param array $tokens
     * @param callable $c1,...
     * @return mixed
     */
    private static function orCallable(array $tokens, callable $c1)
    {
        if (func_num_args() <= 1) {
            return null;
        }

        $p = call_user_func($c1, $tokens);
        if ($p != null) {
            return $p;
        }

        $a = func_get_args();
        $t = array_shift($a);
        array_shift($a);
        array_unshift($a, $t);
        return call_user_func_array('orCallable', $a);

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
        $t = array_shift($a);
        array_unshift($a, 0);
        array_unshift($a, $t);
        $args = call_user_func_array('concatCallableHelper', $a);
        $newArgs = [];
        foreach($args as $arg){
            if(!is_object($arg)){
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

    private static function concatCallableHelper(array $tokens, callable $c1)
    {
        $n = func_num_args();

        if (count($tokens) < $n - 1) {
            return null;
        }

        if ($n == 2) {
            return [$c1($tokens)];
        }

        for ($i = 1; $i < count($tokens); $i++) {

            $r = call_user_func($c1, array_splice($tokens, 0, $i));
            if ($r == null) {
                continue;
            }
            $a = array_merge([array_slice($tokens, $i)], array_splice(func_get_args(), 2));
            $rest = call_user_func_array('concatCallableHelper', $a);
            if (in_array(null, $rest)) {
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


}