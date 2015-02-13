<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/25/14
 * Time: 1:15 PM
 */

namespace ChristianBudde\Part\controller\function_string;


class LexerImpl implements Lexer
{

    private static $patterns = [
        self::T_NULL => "null",
        self::T_BOOL => "true|false",
        self::T_NAME_NOT_STARTING_WITH_UNDERSCORE => "[a-zA-Z][A-Za-z0-9_]*",
        self::T_NAME => "[a-zA-Z_][A-Za-z0-9_]*",
        self::T_OCTAL => "0[0-7]+",
        self::T_HEXADECIMAL => "0x[0-9A-Fa-f]+",
        self::T_BINARY => "0b[0-1]+",
        self::T_EXP_DOUBLE_NUMBER => "([0-9]+|([0-9]*[\\.][0-9]+)|([0-9]+[\\.][0-9]*))[eE][+-]?[0-9]+",
        self::T_DOUBLE_NUMBER => "([0-9]*[\\.][0-9]+)|([0-9]+[\\.][0-9]*)",
        self::T_DECIMAL => "[0-9]+",
        self::T_DOUBLE_QUOTED_STRING => '"(?:[^\\"]|\\.)*"',
        self::T_SINGLE_QUOTED_STRING => "'(?:[^\\']|\\.)*'",
        self::T_SIGN => "[+-]",
        self::T_L_PAREN => "\\(",
        self::T_R_PAREN => "\\)",
        self::T_L_BRACKET => "\\[",
        self::T_R_BRACKET => "\\]",
        self::T_DOT => "\\.",
        self::T_COMMA => ",",
        self::T_COLON => ":",
        self::T_WHITESPACE => "\\s+",
        self::T_DOUBLE_ARROW => "=>",
        self::T_BACKSLASH => "\\\\"

    ];

    /**
     * @param $input
     * @return array
     */
    public static function lex($input)
    {
        $tokens = [];
        while (strlen($input) > 0) {
            if (($m = self::match($input)) === false) {
                return null;
            }

            $tokens[] = $m;
            $input = substr($input, strlen($m['match']));

        }
        $tokens[] = ['match'=> '', 'token'=>self::T_EOF];
        return $tokens;
    }


    private static function match($input)
    {
        $largestLength = 0;
        $largest = false; 
        foreach (self::$patterns as $symbol => $pattern) {
            if (preg_match("/^($pattern)/",$input , $matches) && ($l = strlen($matches[1])) > $largestLength) {
                $largest = [
                    "match" => $matches[1],
                    "token" => $symbol
                ];
                $largestLength = $l;
            }
        }
        return $largest;

    }
}