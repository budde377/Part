<?php
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\function_string\ast\FunctionCallImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NameNotStartingWithUnderscoreImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NoArgumentNamedFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\LexerImpl;
use ChristianBudde\cbweb\controller\function_string\ParserImpl;
use PHPUnit_Framework_TestCase;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 5:13 PM
 */
class FunctionStringParserImplTest extends PHPUnit_Framework_TestCase
{

    public function testParseProgram(){
        $l = LexerImpl::lex("A.f()");
        $p = ParserImpl::parse($l);
        $this->assertEquals(new FunctionCallImpl(new NameNotStartingWithUnderscoreImpl("A"), new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("f"))), $p);
    }

    public function testParseProgramWithWhitespace(){
        $l = LexerImpl::lex("A

        .   f (
         )");
        $p = ParserImpl::parse($l);
        $this->assertEquals(new FunctionCallImpl(new NameNotStartingWithUnderscoreImpl("A"), new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("f"))), $p);
    }

}