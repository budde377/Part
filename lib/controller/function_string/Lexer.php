<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/25/14
 * Time: 11:47 AM
 */

namespace ChristianBudde\cbweb\controller\function_string;


interface Lexer {

    const T_NAME = "T_NAME";
    const T_NAME_NOT_STARTING_WITH_UNDERSCORE = "T_NAME_NOT_STARTING_WITH_UNDERSCORE";
    const T_OCTAL = "T_OCTAL";
    const T_HEXADECIMAL = "T_HEXADECIMAL";
    const T_BINARY = "T_BINARY";
    const T_DECIMAL = "T_DECIMAL";
    const T_EXP_DOUBLE_NUMBER = "T_EXP_DOUBLE_NUMBER";
    const T_DOUBLE_NUMBER = "T_DOUBLE_NUMBER";
    const T_DOUBLE_QUOTED_STRING = "T_DOUBLE_QUOTED_STRING";
    const T_SINGLE_QUOTED_STRING = "T_SINGLE_QUOTED_STRING";
    const T_NULL = "T_NULL";
    const T_DOT = "T_DOT";
    const T_L_PAREN = "T_L_PAREN";
    const T_R_PAREN = "T_R_PAREN";
    const T_L_BRACKET = "T_L_BRACKET";
    const T_R_BRACKET = "T_R_BRACKET";
    const T_COMMA = "T_COMMA";
    const T_COLON = "T_COLON";
    const T_WHITESPACE = "T_WHITESPACE";
    const T_DOUBLE_ARROW = "T_DOUBLE_ARROW";
    const T_BACKSLASH = "T_BACKSLASH";
    const T_BOOL = "T_BOOL";
    const T_SIGN = "T_SIGN";


    /**
     * @param $string
     * @return array
     */
    public static function lex($string);

} 