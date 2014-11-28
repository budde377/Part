<?php
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\function_string\ast\ArgumentImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ArgumentNamedFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ArgumentsImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ArrayAccessFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ArrayEntriesImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ArrayEntryImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ArrayImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ChainCompositeFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\CompositeChainCompositeFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\CompositeFunctionCallImpl;
use ChristianBudde\cbweb\controller\function_string\ast\DecimalImpl;
use ChristianBudde\cbweb\controller\function_string\ast\FunctionCallImpl;
use ChristianBudde\cbweb\controller\function_string\ast\FunctionChainsImpl;
use ChristianBudde\cbweb\controller\function_string\ast\KeyArrowValueImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NamedArgumentImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NameImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NameNotStartingWithUnderscoreImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NoArgumentNamedFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\StringImpl;
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

    public function testParseProgram()
    {
        $l = LexerImpl::lex("A.f()");
        $p = ParserImpl::parse($l);
        $this->assertEquals(new FunctionCallImpl(new NameNotStartingWithUnderscoreImpl("A"), new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("f"))), $p);
    }

    public function testParseFunctionChain()
    {
        $l = LexerImpl::lex("A.f().g()");
        $p = ParserImpl::parse($l);
        $this->assertEquals(new FunctionCallImpl(new FunctionCallImpl(new NameNotStartingWithUnderscoreImpl("A"), new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("f"))), new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl('g'))), $p);
    }

    public function testParseArrayAccessFunction()
    {
        $l = LexerImpl::lex("A[1]");
        $p = ParserImpl::parse($l);
        $this->assertEquals(new FunctionCallImpl(new NameNotStartingWithUnderscoreImpl("A"), new ArrayAccessFunctionImpl(new DecimalImpl(1))), $p);
    }

    public function testParseProgramWithWhitespace()
    {
        $l = LexerImpl::lex("A

        .   f (
         )");
        $p = ParserImpl::parse($l);
        $this->assertEquals(new FunctionCallImpl(new NameNotStartingWithUnderscoreImpl("A"), new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("f"))), $p);
    }

    public function testParseProgramWithUnderscore()
    {
        $l = LexerImpl::lex("_A._f()");
        $p = ParserImpl::parse($l);
        $this->assertEquals(new FunctionCallImpl(new NameImpl("_A"), new NoArgumentNamedFunctionImpl(new NameImpl("_f"))), $p);
    }

    public function testParseCompositeFunctionCall()
    {
        $l = LexerImpl::lex("A..f()..g()");
        $p = ParserImpl::parse($l);
        $this->assertEquals(
            new CompositeFunctionCallImpl(
                new NameNotStartingWithUnderscoreImpl("A"),
                new CompositeChainCompositeFunctionImpl(
                    new ChainCompositeFunctionImpl(
                        new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("f"))),
                    new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("g")))), $p);

    }

    public function testParseMoreCompositeFunctionCall()
    {
        $l = LexerImpl::lex("A..f()..g()..h()");
        $p = ParserImpl::parse($l);
        $this->assertEquals(
            new CompositeFunctionCallImpl(
                new NameNotStartingWithUnderscoreImpl("A"),
                new CompositeChainCompositeFunctionImpl(
                    new CompositeChainCompositeFunctionImpl(
                        new ChainCompositeFunctionImpl(
                            new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("f"))),
                        new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("g"))),
                    new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("h"))
                )), $p);

    }


    public function testParseMoreCompositeFunctionCallWithArrayAccess()
    {
        $l = LexerImpl::lex("A..f()..g()[0]..h()");
        $p = ParserImpl::parse($l);
        $this->assertEquals(
            new CompositeFunctionCallImpl(
                new NameNotStartingWithUnderscoreImpl("A"),
                new CompositeChainCompositeFunctionImpl(
                    new CompositeChainCompositeFunctionImpl(
                        new ChainCompositeFunctionImpl(
                            new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("f"))),
                        new FunctionChainsImpl(new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("g")), new ArrayAccessFunctionImpl(new DecimalImpl(0)))),
                    new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("h"))
                )), $p);

    }

    public function testParseMoreComplexFunctionCall()
    {
        $l = LexerImpl::lex("A..f()..g()..a().b().c()");
        $p = ParserImpl::parse($l);
        $this->assertEquals(
            new CompositeFunctionCallImpl(new NameNotStartingWithUnderscoreImpl('A'),
                new CompositeChainCompositeFunctionImpl(
                    new CompositeChainCompositeFunctionImpl(
                        new ChainCompositeFunctionImpl(new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("f"))),
                        new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("g"))),
                    new FunctionChainsImpl(
                        new FunctionChainsImpl(
                            new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("a")),
                            new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("b"))),
                        new NoArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl("c"))))), $p);

    }


    public function testFunctionWithArguments()
    {
        $l = LexerImpl::lex("A.f('asd', 1, a:5)");
        $p = ParserImpl::parse($l);
        $this->assertEquals(new FunctionCallImpl(
            new NameNotStartingWithUnderscoreImpl('A'),
            new ArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl('f'),
                new ArgumentsImpl(new ArgumentImpl(new StringImpl('asd')),
                    new ArgumentsImpl(new ArgumentImpl(new DecimalImpl(1)),
                        new NamedArgumentImpl(new NameNotStartingWithUnderscoreImpl('a'), new DecimalImpl(5)))))
        ), $p);
    }


    public function testArrays()
    {
        $l = LexerImpl::lex("A.f([1,2,3, 4=>5])");
        $p = ParserImpl::parse($l);
        $this->assertEquals(new FunctionCallImpl(
            new NameNotStartingWithUnderscoreImpl('A'),
            new ArgumentNamedFunctionImpl(new NameNotStartingWithUnderscoreImpl('f'),
                new ArgumentImpl(
                    new ArrayImpl(
                        new ArrayEntriesImpl(new ArrayEntryImpl(new DecimalImpl(1)),
                            new ArrayEntriesImpl(new ArrayEntryImpl(new DecimalImpl(2)),
                                new ArrayEntriesImpl(new ArrayEntryImpl(new DecimalImpl(3)),
                                    new KeyArrowValueImpl(new DecimalImpl(4), new DecimalImpl(5))))))
                ))), $p);

    }
}