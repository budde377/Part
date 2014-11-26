<?php
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\function_string\ast\ChainCompositeFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\CompositeChainCompositeFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\CompositeFunctionCallImpl;
use ChristianBudde\cbweb\controller\function_string\ast\FunctionCallImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NameImpl;
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

    public function testParseProgramWithUnderscore(){
        $l = LexerImpl::lex("_A._f()");
        $p = ParserImpl::parse($l);
        $this->assertEquals(new FunctionCallImpl(new NameImpl("_A"), new NoArgumentNamedFunctionImpl(new NameImpl("_f"))), $p);
    }

    public function testParseCompositeFunctionCall(){
        $l = LexerImpl::lex("A..f()..g()");
        $p = ParserImpl::parse($l);
        $this->assertEquals(new CompositeFunctionCallImpl(new NameNotStartingWithUnderscoreImpl("A"), new CompositeChainCompositeFunctionImpl(new ChainCompositeFunctionImpl(new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("f"))),new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("g")))), $p);

    }

}