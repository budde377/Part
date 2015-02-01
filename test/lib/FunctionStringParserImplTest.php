<?php
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\function_string\ast\ArgumentsImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ArrayAccessFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\ArrayEntriesImpl;
use ChristianBudde\cbweb\controller\function_string\ast\BoolScalarImpl;
use ChristianBudde\cbweb\controller\function_string\ast\CompositeFunctionProgramImpl;
use ChristianBudde\cbweb\controller\function_string\ast\CompositeFunctionsImpl;
use ChristianBudde\cbweb\controller\function_string\ast\DecimalUnsignedNumScalarImpl;
use ChristianBudde\cbweb\controller\function_string\ast\DoubleQuotedStringScalarImpl;
use ChristianBudde\cbweb\controller\function_string\ast\FunctionChainCompositeFunctionProgramImpl;
use ChristianBudde\cbweb\controller\function_string\ast\FunctionChainProgramImpl;
use ChristianBudde\cbweb\controller\function_string\ast\FunctionChainsImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NamedArgumentImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NamedArrayEntriesImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NamedArrayEntryImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NamedFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NameImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NameNotStartingWithUnderscoreImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NoArgumentNamedFunctionImpl;
use ChristianBudde\cbweb\controller\function_string\ast\NonEmptyArrayImpl;
use ChristianBudde\cbweb\controller\function_string\ast\SingleQuotedStringScalarImpl;
use ChristianBudde\cbweb\controller\function_string\LexerImpl;
use ChristianBudde\cbweb\controller\function_string\Parser;
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
    /** @var  Parser */
    private $parser;

    protected function setUp()
    {
        parent::setUp();
        $this->parser = new ParserImpl();
    }


    public function testParseProgram()
    {
        $l = LexerImpl::lex("A.f()");
        $p = $this->parser->parse($l);
        $this->assertEquals(new FunctionChainProgramImpl(
            new NameNotStartingWithUnderscoreImpl('A'),
            new NoArgumentNamedFunctionImpl(
                new NameNotStartingWithUnderscoreImpl('f'))), $p);
    }

    public function testParseCompositeFunctionOnFunctionChain()
    {
        $l = LexerImpl::lex("A.f()..g()..h()");
        $p = $this->parser->parse($l);
        $this->assertEquals(
            new FunctionChainCompositeFunctionProgramImpl(
                new NameNotStartingWithUnderscoreImpl('A'),
                new NoArgumentNamedFunctionImpl(
                    new NameNotStartingWithUnderscoreImpl('f')),
                new CompositeFunctionsImpl(
                    new NoArgumentNamedFunctionImpl(
                        new NameNotStartingWithUnderscoreImpl('g')),
                    new NoArgumentNamedFunctionImpl(
                        new NameNotStartingWithUnderscoreImpl('h')))), $p);

    }

    public function testParseProgramWithBoolArgument()
    {
        $l = LexerImpl::lex("A.f(true)");
        $p = $this->parser->parse($l);
        $this->assertEquals(new FunctionChainProgramImpl(
            new NameNotStartingWithUnderscoreImpl('A'),
            new NamedFunctionImpl(
                new NameNotStartingWithUnderscoreImpl('f'),
                new BoolScalarImpl('true'))), $p);
    }

    public function testParseFunctionChain()
    {
        $l = LexerImpl::lex("A.f().g()");
        $p = $this->parser->parse($l);
        $this->assertEquals(new FunctionChainProgramImpl(
            new NameNotStartingWithUnderscoreImpl('A'),
            new FunctionChainsImpl(new NoArgumentNamedFunctionImpl(
                new NameNotStartingWithUnderscoreImpl('f')),
                new NoArgumentNamedFunctionImpl(
                    new NameNotStartingWithUnderscoreImpl('g')))), $p);
    }

    public function testParseArrayAccessFunction()
    {
        $l = LexerImpl::lex("A[1]");
        $p = $this->parser->parse($l);
        $this->assertEquals(new FunctionChainProgramImpl(
            new NameNotStartingWithUnderscoreImpl('A'),
            new ArrayAccessFunctionImpl(new DecimalUnsignedNumScalarImpl('1'))
        ), $p);
    }

    public function testParseProgramWithWhitespace()
    {
        $l = LexerImpl::lex("A

        .   f (
         )");
        $p = $this->parser->parse($l);
        $this->assertEquals(new FunctionChainProgramImpl(
            new NameNotStartingWithUnderscoreImpl('A'),
            new NoArgumentNamedFunctionImpl(
                new NameNotStartingWithUnderscoreImpl('f'))), $p);
    }

    public function testParseProgramWithUnderscore()
    {
        $l = LexerImpl::lex("_A._f()");
        $p = $this->parser->parse($l);
        $this->assertEquals(new FunctionChainProgramImpl(
            new NameImpl('_A'),
            new NoArgumentNamedFunctionImpl(
                new NameImpl('_f'))), $p);
    }

    public function testParseCompositeFunctionCall()
    {
        $l = LexerImpl::lex("A..f()..g()");
        $p = $this->parser->parse($l);
        $this->assertEquals(
            new CompositeFunctionProgramImpl(
                new NameNotStartingWithUnderscoreImpl('A'),
                new CompositeFunctionsImpl(
                    new NoArgumentNamedFunctionImpl(
                        new NameNotStartingWithUnderscoreImpl('f')),
                    new NoArgumentNamedFunctionImpl(
                        new NameNotStartingWithUnderscoreImpl('g')))), $p);

    }

    public function testParseLongFS()
    {
        $l = LexerImpl::lex('PageOrder..setPageOrder(PageOrder.getPage("hjem"), 0 )..setPageOrder(PageOrder.getPage("arrangementer"), 1 )..setPageOrder(PageOrder.getPage("nyheder"), 2 )..setPageOrder(PageOrder.getPage("nyheder"), 3 )..setPageOrder(PageOrder.getPage("nyheder"), 4 )..setPageOrder(PageOrder.getPage("nyheder"), 5 )..getPageOrder()');
        $p = $this->parser->parse($l);

        $this->assertEquals(
            new CompositeFunctionProgramImpl(
                new NameNotStartingWithUnderscoreImpl('PageOrder'),
                new CompositeFunctionsImpl(
                    new NamedFunctionImpl(
                        new NameNotStartingWithUnderscoreImpl('setPageOrder'),
                        new ArgumentsImpl(
                            new FunctionChainProgramImpl(
                                new NameNotStartingWithUnderscoreImpl('PageOrder'),
                                new NamedFunctionImpl(
                                    new NameNotStartingWithUnderscoreImpl('getPage'),
                                    new DoubleQuotedStringScalarImpl('"hjem"'))),
                            new DecimalUnsignedNumScalarImpl('0'))
                    ),

                    new CompositeFunctionsImpl(
                        new NamedFunctionImpl(
                            new NameNotStartingWithUnderscoreImpl('setPageOrder'),
                            new ArgumentsImpl(
                                new FunctionChainProgramImpl(
                                    new NameNotStartingWithUnderscoreImpl('PageOrder'),
                                    new NamedFunctionImpl(
                                        new NameNotStartingWithUnderscoreImpl('getPage'),
                                        new DoubleQuotedStringScalarImpl('"arrangementer"'))),
                                new DecimalUnsignedNumScalarImpl('1'))),
                        new CompositeFunctionsImpl(
                            new NamedFunctionImpl(
                                new NameNotStartingWithUnderscoreImpl('setPageOrder'),
                                new ArgumentsImpl(
                                    new FunctionChainProgramImpl(
                                        new NameNotStartingWithUnderscoreImpl('PageOrder'),
                                        new NamedFunctionImpl(
                                            new NameNotStartingWithUnderscoreImpl('getPage'),
                                            new DoubleQuotedStringScalarImpl('"nyheder"'))),
                                    new DecimalUnsignedNumScalarImpl('2'))),
                            new CompositeFunctionsImpl(
                                new NamedFunctionImpl(
                                    new NameNotStartingWithUnderscoreImpl('setPageOrder'),
                                    new ArgumentsImpl(
                                        new FunctionChainProgramImpl(
                                            new NameNotStartingWithUnderscoreImpl('PageOrder'),
                                            new NamedFunctionImpl(
                                                new NameNotStartingWithUnderscoreImpl('getPage'),
                                                new DoubleQuotedStringScalarImpl('"nyheder"'))),
                                        new DecimalUnsignedNumScalarImpl('3'))),
                                new CompositeFunctionsImpl(
                                    new NamedFunctionImpl(
                                        new NameNotStartingWithUnderscoreImpl('setPageOrder'),
                                        new ArgumentsImpl(
                                            new FunctionChainProgramImpl(
                                                new NameNotStartingWithUnderscoreImpl('PageOrder'),
                                                new NamedFunctionImpl(
                                                    new NameNotStartingWithUnderscoreImpl('getPage'),
                                                    new DoubleQuotedStringScalarImpl('"nyheder"'))),
                                            new DecimalUnsignedNumScalarImpl('4'))),
                                    new CompositeFunctionsImpl(
                                        new NamedFunctionImpl(
                                            new NameNotStartingWithUnderscoreImpl('setPageOrder'),
                                            new ArgumentsImpl(
                                                new FunctionChainProgramImpl(
                                                    new NameNotStartingWithUnderscoreImpl('PageOrder'),
                                                    new NamedFunctionImpl(
                                                        new NameNotStartingWithUnderscoreImpl('getPage'),
                                                        new DoubleQuotedStringScalarImpl('"nyheder"'))),
                                                new DecimalUnsignedNumScalarImpl('5'))),
                                        new NoArgumentNamedFunctionImpl(
                                            new NameNotStartingWithUnderscoreImpl('getPageOrder'))))))
                    ))), $p);
    }

    public function testParseMoreCompositeFunctionCall()
    {
        $l = LexerImpl::lex("A..f()..g()..h()");
        $p = $this->parser->parse($l);
        $this->assertEquals(
            new CompositeFunctionProgramImpl(
                new NameNotStartingWithUnderscoreImpl('A'),
                new CompositeFunctionsImpl(
                    new NoArgumentNamedFunctionImpl(
                        new NameNotStartingWithUnderscoreImpl('f')),
                    new CompositeFunctionsImpl(
                        new NoArgumentNamedFunctionImpl(
                            new NameNotStartingWithUnderscoreImpl('g')),
                        new NoArgumentNamedFunctionImpl(
                            new NameNotStartingWithUnderscoreImpl('h'))))), $p);
    }


    public function testParseMoreCompositeFunctionCallWithArrayAccess()
    {
        $l = LexerImpl::lex("A..f()..g()[0]..h()");
        $p = $this->parser->parse($l);
        $this->assertEquals(
            new CompositeFunctionProgramImpl(
                new NameNotStartingWithUnderscoreImpl('A'),
                new CompositeFunctionsImpl(
                    new NoArgumentNamedFunctionImpl(
                        new NameNotStartingWithUnderscoreImpl('f')),
                    new CompositeFunctionsImpl(
                        new FunctionChainsImpl(
                            new NoArgumentNamedFunctionImpl(
                                new NameNotStartingWithUnderscoreImpl('g')),
                            new ArrayAccessFunctionImpl(new DecimalUnsignedNumScalarImpl('0'))),
                        new NoArgumentNamedFunctionImpl(
                            new NameNotStartingWithUnderscoreImpl('h'))))), $p);
    }

    public function testParseMoreComplexFunctionCall()
    {
        $l = LexerImpl::lex("A..f()..g()..a().b().c()");
        $p = $this->parser->parse($l);
        $this->assertEquals(
            new CompositeFunctionProgramImpl(
                new NameNotStartingWithUnderscoreImpl('A'),
                new CompositeFunctionsImpl(
                    new NoArgumentNamedFunctionImpl(
                        new NameNotStartingWithUnderscoreImpl('f')),
                    new CompositeFunctionsImpl(
                        new NoArgumentNamedFunctionImpl(
                            new NameNotStartingWithUnderscoreImpl('g')),
                        new FunctionChainsImpl(
                            new NoArgumentNamedFunctionImpl(
                                new NameNotStartingWithUnderscoreImpl('a')),
                            new FunctionChainsImpl(
                                new NoArgumentNamedFunctionImpl(
                                    new NameNotStartingWithUnderscoreImpl('b')),
                                new NoArgumentNamedFunctionImpl(
                                    new NameNotStartingWithUnderscoreImpl('c'))))))), $p);
    }


    public function testFunctionWithArguments()
    {
        $l = LexerImpl::lex("A.f('asd', 1, a:5)");
        $p = $this->parser->parse($l);
        $this->assertEquals(new FunctionChainProgramImpl(
            new NameNotStartingWithUnderscoreImpl('A'),
            new NamedFunctionImpl(
                new NameNotStartingWithUnderscoreImpl('f'),
                new ArgumentsImpl(
                    new SingleQuotedStringScalarImpl("'asd'"),
                    new ArgumentsImpl(
                        new DecimalUnsignedNumScalarImpl('1'),
                        new NamedArgumentImpl(
                            new NameNotStartingWithUnderscoreImpl('a'),
                            new DecimalUnsignedNumScalarImpl('5')))))), $p);

    }


    public function testArrays()
    {
        $l = LexerImpl::lex("A.f([1,2,3, 4=>5])");
        $p = $this->parser->parse($l);
        $this->assertEquals(new FunctionChainProgramImpl(
            new NameNotStartingWithUnderscoreImpl('A'),
            new NamedFunctionImpl(
                new NameNotStartingWithUnderscoreImpl('f'),
                new NonEmptyArrayImpl(
                    new ArrayEntriesImpl(
                        new DecimalUnsignedNumScalarImpl('1'),
                        new ArrayEntriesImpl(
                            new DecimalUnsignedNumScalarImpl('2'),
                            new ArrayEntriesImpl(
                                new DecimalUnsignedNumScalarImpl('3'),
                                new NamedArrayEntryImpl(
                                    new DecimalUnsignedNumScalarImpl('4'),
                                    new DecimalUnsignedNumScalarImpl('5')))))))), $p);
    }

    public function testArrays2()
    {
        $l = LexerImpl::lex("A.f([4=>5,1])");
        $p = $this->parser->parse($l);
        $this->assertEquals(new FunctionChainProgramImpl(
            new NameNotStartingWithUnderscoreImpl('A'),
            new NamedFunctionImpl(
                new NameNotStartingWithUnderscoreImpl('f'),
                new NonEmptyArrayImpl(
                    new NamedArrayEntriesImpl(
                        new DecimalUnsignedNumScalarImpl('4'),
                        new DecimalUnsignedNumScalarImpl('5'),
                        new DecimalUnsignedNumScalarImpl('1'))))), $p);
    }

    public function testReturnsNullOnInvalidProgram()
    {
        $p = $this->parser->parseString('A.f(_a:123)');
        $this->assertNull($p);
    }


    public function testReturnsNullOnInvalidProgram2()
    {
        $p = $this->parser->parseString('A.f(a:123, 1)');
        $this->assertNull($p);

    }
}