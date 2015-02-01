<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 11/25/14
 * Time: 1:15 PM
 */

namespace ChristianBudde\cbweb\test;


use ChristianBudde\cbweb\controller\function_string\Lexer;
use ChristianBudde\cbweb\controller\function_string\LexerImpl;

class FunctionStringLexerImplTest extends \PHPUnit_Framework_TestCase
{


    public function testLexerLexesWhitespace()
    {
        $r = LexerImpl::lex(" ");
        $this->assertEquals([Lexer::T_WHITESPACE, Lexer::T_EOF], $this->simplify($r));
    }

    public function testLexerGetsNumbersRight()
    {
        $r = LexerImpl::lex("0.1 0.1e123 1 0xabC 0b010 0123 09");
        $this->assertEquals([
            Lexer::T_DOUBLE_NUMBER, Lexer::T_WHITESPACE,
            Lexer::T_EXP_DOUBLE_NUMBER, Lexer::T_WHITESPACE,
            Lexer::T_DECIMAL, Lexer::T_WHITESPACE,
            Lexer::T_HEXADECIMAL, Lexer::T_WHITESPACE,
            Lexer::T_BINARY, Lexer::T_WHITESPACE,
            Lexer::T_OCTAL, Lexer::T_WHITESPACE,
            Lexer::T_DECIMAL, Lexer::T_EOF

        ], $this->simplify($r));
    }

    public function testLexerGetsNamesRight(){
        $r = LexerImpl::lex("_hej hej");
        $this->assertEquals([
            Lexer::T_NAME, Lexer::T_WHITESPACE,
            Lexer::T_NAME_NOT_STARTING_WITH_UNDERSCORE, Lexer::T_EOF

        ], $this->simplify($r));

    }

    public function testLexerGetsSymbolsRight(){
        $r = LexerImpl::lex(",.()][:=>\\");
        $this->assertEquals([
            Lexer::T_COMMA,
            Lexer::T_DOT,
            Lexer::T_L_PAREN,
            Lexer::T_R_PAREN,
            Lexer::T_R_BRACKET,
            Lexer::T_L_BRACKET,
            Lexer::T_COLON,
            Lexer::T_DOUBLE_ARROW,
            Lexer::T_BACKSLASH, Lexer::T_EOF
        ], $this->simplify($r));

    }


    public function simplify(array $r)
    {
        return array_map(function (array $a) {
            return $a['token'];
        }, $r);
    }

} 